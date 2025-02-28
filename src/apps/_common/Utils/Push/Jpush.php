<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/24
 * Time: 12:06
 */

namespace Common\Utils\Push;


use Api\Models\Inbox\Inbox;
use Common\Utils\Data\StringHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\Helper;
use JPush\Client;
use JPush\Exceptions\APIConnectionException;

/**
 * Class Jpush
 * @package Common\Utils\Push
 * @phan-file-suppress PhanUndeclaredClassMethod, PhanUndeclaredClassCatch
 */
class Jpush
{
    use Helper;

    const TIME_TO_LIVE_ONE_DAY = 8640000;  //推送保留时间 10天
    const TYPE_INBOX = 'inbox'; //跳私信详情
    const TYPE_NOTICE = 'notice'; //跳公告详情
    const TYPE_MODEL = 'modal'; //还款成功弹窗
    const TYPE_MODAL_DAIKOU_FAILED = 'modal_daikou_failed'; //扣款失败弹窗

    /**
     * 跳转到首页
     * @param $title
     * @param $content
     * @param string $user_id 多个以逗号分隔
     * @param array $custom
     * @param string $platform
     * @param int $bigPushDuration 分钟
     * @return array|bool|mixed
     */
    public static function pushHome(
        $title,
        $content,
        $user_id,
        $custom = [],
        $platform = "all",
        $bigPushDuration = 0
    ) {
        // 生成私信
        Inbox::model()->create($user_id, $title, $content);
        return self::pushUser($title, $content, $user_id, $custom, $platform, $bigPushDuration);
    }

    /**
     * 跳转到私信
     * @param $title
     * @param $content
     * @param $user_id
     * @param string $platform
     * @param int $bigPushDuration
     * @return array|bool|mixed
     */
    public static function pushInbox($title, $content, $user_id, $platform = "all", $bigPushDuration = 0)
    {
        // 生成私信
        $inbox = Inbox::model()->create($user_id, $title, $content);

        // 推送消息
        $custom = array("type" => self::TYPE_INBOX, "id" => $inbox->id);
        return self::pushUser($title, $content, $user_id, $custom, $platform, $bigPushDuration);
    }

    /**
     * 跳转到公告
     * @param $title
     * @param $content
     * @param $noticeId
     * @param string $platform
     * @param int $bigPushDuration
     * @return array|bool|mixed
     */
    public static function pushNotice($title, $content, $noticeId, $platform = "all", $bigPushDuration = 0)
    {
        // 推送消息
        $custom = array("type" => self::TYPE_NOTICE, "id" => $noticeId);
        return self::pushUser($title, $content, null, $custom, $platform, $bigPushDuration);
    }

    /**
     * 推送弹窗
     * @param $title
     * @param $content
     * @param $user_id
     * @param string $platform
     * @param int $bigPushDuration
     * @param $type
     * @return array|bool|mixed
     */
    public static function pushModal($title, $content, $user_id, $type = self::TYPE_MODEL, $platform = "all", $bigPushDuration = 0)
    {
        // 生成私信
        Inbox::model()->create($user_id, $title, $content);
        $custom = array("type" => $type);
        return self::pushUser($title, $content, $user_id, $custom, $platform, $bigPushDuration);
    }

    /**
     * 广播所有用户
     * @param string $title 标题，android独有
     * @param string $content 内容：android和ios公用
     * @param array $custom 键值对，可以根据业务需要进行定制，但必须包含type键，比如array("type"=>"notice","noticeId"=>1)
     * @param string $platform 平台：android/ios/all，默认是all
     * @param int $bigPushDuration
     * @return array|bool|mixed
     */
    public static function pushAll($title, $content, $custom = array(), $platform = "all", $bigPushDuration = 0)
    {
        return self::pushUser($title, $content, null, $custom, $platform, $bigPushDuration);
    }

    /**
     * 根据用户id和平台来推送给所有用户
     * @param string $title 标题，android独有
     * @param string $content 内容：android和ios公用
     * @param string|null $user_id 用户id 多个用户的时候需要用半角逗号隔开。
     * @param array $custom 键值对，可以根据业务需要进行定制，但必须包含type键，比如array("type"=>"inbox","inboxId"=>1)
     * @param string $platform 平台：android/ios/all，默认是all
     *  文档 https://github.com/jpush/jpush-api-php-client/blob/61c6bc6fad30ebb41b28870b49eeebb90e9c162e/doc/api.md
     * @param int $bigPushDuration
     * @return array|bool|mixed
     */
    public static function pushUser($title, $content, $user_id, $custom = [], $platform = "all", $bigPushDuration = 0)
    {

        $companySign = 'saas';
        $content = StringHelper::delSpace($content);
        if (!is_null($user_id)) {
            $user_id = explode(',', $user_id);
            foreach ($user_id as $k => $v) {
                $user_id[$k] = $companySign . $v; //用户ID加上前缀
            }
        }
        $ios_notification = array(
            'sound' => '',
            'badge' => '+1',
            'content-available' => true,
            'category' => $companySign,
            'extras' => $custom
        );
        $android_notification = array(
            'title' => $title,
            'builder_id' => 2,
            'extras' => $custom
        );
        $message = array(
            'title' => $title,
            'content_type' => 'text',
            'extras' => $custom
        );
        $options = array(
            'time_to_live' => self::TIME_TO_LIVE_ONE_DAY,
            //默认 86400 （1 天），最长 10 天。设置为 0 表示不保留离线消息，只有推送当前在线的用户可以收到
            'apns_production' => app()->environment() == 'prod' ? true : false,
            // apns_production: 表示APNs是否生产环境， True 表示推送生产环境，False 表示要推送开发环境；如果不指定则默认为推送生产环境
            'big_push_duration' => $bigPushDuration,
            //表示定速推送时长(分钟)，又名缓慢推送，把原本尽可能快的推送速度，降低下来，给定的 n 分钟内，均匀地向这次推送的目标用户推送。最大值为1400.未设置则不是定速推送
        );
        $response = true;
        try {
            $app_key = env('JIGUANG_APP_KEY');
            $master_secret = env('JIGUANG_SECTET_KEY');
            $client = new Client($app_key, $master_secret, base_path('storage/logs/jpush.log'));
            $push = $client->push();
            if (is_null($user_id)) {
                $response = $push->setPlatform($platform)
                    ->addAllAudience()
                    ->setNotificationAlert($content)
                    ->iosNotification($content, $ios_notification)
                    ->androidNotification($content, $android_notification)
                    ->message($content, $message)
                    ->options($options)
                    ->send();
            } else {
                $response = $push->setPlatform($platform)
                    ->addAlias($user_id)
                    ->setNotificationAlert($content)
                    ->iosNotification($content, $ios_notification)
                    ->androidNotification($content, $android_notification)
                    ->message($content, $message)
                    ->options($options)
                    ->send();
            }
        } catch (APIConnectionException $e) {
            EmailHelper::sendException($e);
        } catch (\Exception $e) {
            EmailHelper::sendException($e);
        }
        return $response;
    }
}
