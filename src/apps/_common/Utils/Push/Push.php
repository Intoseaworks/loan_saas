<?php

namespace Common\Utils\Push;

use Common\Models\Inbox\Inbox;
use Common\Models\User\User;
use Common\Utils\Helper;
use Common\Utils\Push\Services\GooglePush;

class Push
{
    use Helper;

    const TIME_TO_LIVE_ONE_DAY = 8640000;  //推送保留时间 10天
    const TYPE_INBOX = 'inbox'; //跳私信详情
    const TYPE_NOTICE = 'notice'; //跳公告详情
    const TYPE_MODEL = 'modal'; //还款成功弹窗
    const TYPE_MODAL_DAIKOU_FAILED = 'modal_daikou_failed'; //扣款失败弹窗
    const TYPE_LOAN_SUCCESS_SCHEDULE = 'loan_success_schedule';//放款成功，日程推送

    private static $_config = null;

    /**
     * 添加推送私信
     *
     * @param $title
     * @param $content
     * @param $userId
     * @param array $custom
     * @param bool $isPopup 是否弹窗
     * @return bool|mixed
     */
    public static function pushInbox($title, $content, $userId, $custom = [], $isPopup = false)
    {
        // 生成私信
        $inbox = Inbox::model()->create($userId, $title, $content);

        // 推送消息
        if (!$custom) {
            $custom["type"] = self::TYPE_INBOX;
        }
        $custom["isPopup"] = $isPopup;
        $custom["url"] = config('config.h5_client_domain') . '/private-message-details/' . $inbox->inbox_id;
        return self::sendMessage($userId, $title, $content, $custom);
    }

    /**
     * 统一推送接口。
     *
     * @param $userId
     * @param $title
     * @param $message
     * @param array $custom 支持极光$custom['client_id'] = ios
     * @return mixed
     */
    public static function sendMessage($userId, $title, $message, $custom = [])
    {
        /** config开关控制推送 */
        if (!self::hasPushOn()) {
            return false;
        }
        $user = User::model()->getOne($userId);
        if (!optional($user->userInfo)->google_token) {
            return false;
        }
        //$class = '\\Common\\Utils\\Push\\Services\\' . ucfirst($platform) . 'Push';
        $push = new GooglePush();
        if (method_exists($push, 'sendMessage')) {
            return $push->sendMessage($user->userInfo->google_token, $title, $message, $custom, optional($user->app)->google_server_key);
        }
        return false;
    }

    /**
     * 设定配置信息。
     * @param $config
     */
    public static function setConfig($config)
    {
        self::$_config = $config;
    }

    /**
     * @return bool
     */
    public static function hasPushOn()
    {
        return config('config.has_app_push_on');
    }
}
