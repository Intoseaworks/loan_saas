<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/24
 * Time: 12:06
 */

namespace Common\Utils\Push\Services;


use Common\Utils\Email\EmailHelper;
use JPush\Client;
use JPush\Exceptions\APIConnectionException;

/**
 * Class JiGuangPush
 * @package Common\Utils\Push\Services
 * @phan-file-suppress PhanUndeclaredClassMethod, PhanUndeclaredClassCatch
 */
class JiGuangPush implements PushInterface
{
    const TIME_TO_LIVE_ONE_DAY = 8640000; //推送保留时间 10天
    private $appKey;
    private $secretKey;

    public function __construct($config)
    {
        $this->appKey = env('JIGUANG_APP_KEY');
        $this->secretKey = env('JIGUANG_SECTET_KEY');
    }

    public function sendMessage($deviceToken, $title, $content, $custom = [])
    {
        /** custom['client_id'] 配置平台发送 ios/android */
        $platform = array_get($custom, 'client_id', 'all');
        $ios_notification = array(
            'sound' => '',
            'badge' => '+1',
            'content-available' => true,
            'category' => '',
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
            'big_push_duration' => 0,
            //表示定速推送时长(分钟)，又名缓慢推送，把原本尽可能快的推送速度，降低下来，给定的 n 分钟内，均匀地向这次推送的目标用户推送。最大值为1400.未设置则不是定速推送
        );
        $response = true;
        try {
            $client = new Client($this->appKey, $this->secretKey, base_path('storage/logs/jpush.log'));
            $push = $client->push();
            if (is_null($deviceToken)) {
                $push->setPlatform($platform)
                    ->addAllAudience();
            } else {
                $push->setPlatform($platform)
                    ->addAlias($deviceToken);
            }
            $response = $push->setNotificationAlert($content)
                ->iosNotification($content, $ios_notification)
                ->androidNotification($content, $android_notification)
                ->message($content, $message)
                ->options($options)
                ->send();
        } catch (APIConnectionException $e) {
            EmailHelper::sendException($e);
        } catch (\Exception $e) {
            EmailHelper::sendException($e);
        }
        return $response;
    }
}
