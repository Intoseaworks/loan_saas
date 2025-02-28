<?php

/**
 * Created by PhpStorm.
 * User: Nio
 * Date: 2020/06/01
 * Time: 10:45
 */

namespace Common\Utils\Sms;

use CMText\TextClient;
use Common\Models\Sms\SmsLog;
use Common\Utils\Data\DateHelper;
use Common\Utils\Helper;
use Admin\Services\Sms\SmsServer;
use Common\Utils\Sms\SmsBukInfobipHelper;
use Common\Utils\Sms\SmsPesoLocalGsmHelper;

class SmsBukHelper {

    use Helper;

    const CHANNEL_INFOBIP = "infobip";
    const CHANNEL_GSM = 'gsm';

    public static function send($mobile, $eventId, $values, $sendId = 'KyatFIN') {
        return SmsBukInfobipHelper::helper()->send($mobile, $eventId, $values, $sendId);
    }

    public static function sendMarketing($mobile, $content, $values = [], $sendId = 'KyatFIN', $eventId = 0) {
        return SmsBukInfobipHelper::helper()->sendMarketing($mobile, $content, $values, $sendId, $eventId);
    }

    public static function getChannelBySendId($sendId) {
        return self::CHANNEL_INFOBIP;
    }

    public static function getMktChannelBySendId($sendId) {
        return self::CHANNEL_INFOBIP;
    }

}
