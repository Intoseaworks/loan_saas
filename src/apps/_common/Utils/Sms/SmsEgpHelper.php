<?php

/**
 * Created by PhpStorm.
 * User: Nio
 * Date: 2020/06/01
 * Time: 10:45
 */

namespace Common\Utils\Sms;

use Common\Utils\Helper;
use Common\Utils\Sms\SmsPesoInfobipHelper;

class SmsEgpHelper {

    use Helper;

    const CHANNEL_INFOBIP = "infobip";
    const CHANNEL_GSM = 'gsm';

    public static function send($mobile, $eventId, $values, $sendId = 'CashCat') {
//        $channel = self::getChannelByOperator($mobile); #根据手机号段获取通道
        $channel = self::getChannelBySendId($sendId); #根据sendId获取通道名
        if ($channel == self::CHANNEL_INFOBIP) {// && in_array(substr($mobile, -10, 3), self::INFOBIP)){
            return SmsPesoInfobipHelper::helper()->send($mobile, $eventId, $values, $sendId);
        }
        return SmsPesoGlobalHelper::helper()->send($mobile, $eventId, $values, $sendId);
    }

    public static function sendMarketing($mobile, $content, $values = [], $sendId = 'CashCat', $eventId = 0) {

        $channel = self::getMktChannelBySendId($sendId); #根据sendId获取通道名

        if ($channel == self::CHANNEL_INFOBIP) {// && in_array(substr($mobile, -10, 3), self::INFOBIP)){
            return SmsPesoInfobipHelper::helper()->sendMarketing($mobile, $content, $values, $sendId, $eventId);
        }
        return SmsPesoGlobalMktHelper::helper()->sendMarketing($mobile, $content, $values, $sendId, $eventId);
    }

    public static function getChannelBySendId($sendId) {
        if (in_array($sendId, ["CashCat--"])) {
            return self::CHANNEL_INFOBIP;
        }
        return false;
    }

    public static function getMktChannelBySendId($sendId) {
        if (in_array($sendId, ["CashCat--"])) {
            return self::CHANNEL_INFOBIP;
        }
        return false;
    }

}
