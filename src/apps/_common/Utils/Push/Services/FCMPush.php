<?php

namespace Common\Utils\Push\Services;

use Common\Utils\Email\EmailHelper;
use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;

/**
 * Google fcm推送
 * https://github.com/brozot/Laravel-FCM
 * Class FCMPush
 */
class FCMPush implements PushInterface
{
    /**
     * 根据用户id和平台来推送给所有用户
     * @param string $title 标题，android独有
     * @param $body
     * @param null|array $token
     * @param array $custom 键值对，可以根据业务需要进行定制，但必须包含type键，比如array("type"=>"inbox","inboxId"=>1)
     * @return array|bool|mixed
     */
    public function sendMessage($token, $title, $body, $custom = [], $serverKey = '')
    {
        try {
            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setTimeToLive(60 * 20);

            $notificationBuilder = new PayloadNotificationBuilder($title);
            $notificationBuilder->setBody($body)
                ->setSound('default');

            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData($custom);

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();

            $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
            return $downstreamResponse->numberSuccess() > 0;
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            exit();
            EmailHelper::sendException($e);
        }
    }

}
