<?php

/**
 * Created by PhpStorm.
 * User: Nio
 * Date: 2020/06/01
 * Time: 10:45
 */

namespace Common\Utils\Sms;

use Common\Models\Sms\SmsLog;
use Common\Utils\Data\DateHelper;
use Common\Utils\Helper;
use Admin\Services\Sms\SmsServer;
use Common\Utils\Curl;
use Yunhan\Utils\Env;
use Common\Utils\MerchantHelper;

class SmsBukInfobipHelper {

    use Helper;

    const CONFIG_PROD = [
        "KyatFIN" => [
            "url" => "https://api.infobip.com/sms/2/text/advanced",
            "account" => "scoreone",
            "password" => "Aa1698123456!", //HTUXMdyWW
        ]
    ];

    public static function getSign($account, $password, $time) {
        return md5($account . $password . $time);
    }

    public static function getConfig($sendId) {
        if (Env::isProd()) {
            return self::CONFIG_PROD[$sendId];
        } else {
            return self::CONFIG_PROD[$sendId];
        }
    }

    public static function send($mobile, $eventId, $values, $sendId = 'SurityCash') {
        $config = self::getConfig($sendId);
        $mobile = "95" . substr($mobile, -10);
        $content = SmsServer::server()->getTplByEventId($eventId);
        if (!$content) {
            $content = t($eventId, "sms-peso");
        }
        foreach ($values as $k => $v) {
            $content = str_replace("{{" . $k . "}}", $v, $content);
        }
        # {"messages":[{"from":"InfoSMS","destinations":[{"to":"41793026727"}],"text":"This is a sample message"}]}
        $sign = base64_encode("{$config['account']}:{$config['password']}");
        $post = ["messages" =>
            [
                "from" => $sendId,
                "destinations" => [
                    ["to" => $mobile]
                ],
                "text" => $content
            ]
        ];
//        echo $url;exit;
        $header = array(
            "Authorization:Basic {$sign}",
            'Content-Type: application/json',
            'Accept: application/json'
        );
        $res = Curl::post(json_encode($post), $config['url'], $header);
        $res = json_decode($res, true);
        $sendStatus = in_array($res['messages'][0]['status']['groupName'], ['ACCEPTED', 'PENDING', 'DELIVERED']) ? 1 : 0;
        $log = [
            "telephone" => $mobile,
            "event_id" => $eventId,
            "send_content" => $content,
            "created_at" => DateHelper::dateTime(),
            "remark" => $sendId,
            "type" => SmsLog::TYPE_SMS,
            "status" => $sendStatus,
            "res" => json_encode($res),
            "sms_channel" => "Infobip"
        ];
        SmsLog::model()->create($log);
        if ('1' == $sendStatus) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function sendMarketing($mobile, $content, $values = [], $sendId = 'SurityCash',$eventId=0) {
        $config = self::getConfig($sendId);
        $mobile = "95" . substr($mobile, -10);
        foreach ($values as $k => $v) {
            $content = str_replace("{{" . $k . "}}", $v, $content);
        }
        # {"messages":[{"from":"InfoSMS","destinations":[{"to":"41793026727"}],"text":"This is a sample message"}]}
        $sign = base64_encode("{$config['account']}:{$config['password']}");
        $post = ["messages" =>
            [
                "from" => $sendId,
                "destinations" => [
                    ["to" => $mobile]
                ],
                "text" => $content
            ]
        ];
//        echo $url;exit;
        $header = array(
            "Authorization:Basic {$sign}",
            'Content-Type: application/json',
            'Accept: application/json'
        );
        $res = Curl::post(json_encode($post), $config['url'], $header);
        $res = json_decode($res, true);
        $sendStatus = in_array($res['messages'][0]['status']['groupName'], ['ACCEPTED', 'PENDING', 'DELIVERED']) ? 1 : 0;
        $log = [
            "telephone" => $mobile,
            "event_id" => $eventId,
            "send_content" => $content,
            "created_at" => DateHelper::dateTime(),
            "remark" => $sendId,
            "type" => SmsLog::TYPE_SMS,
            "status" => $sendStatus,
            "res" => json_encode($res),
            "sms_channel" => "Infobip"
        ];
        SmsLog::model()->create($log);
        if ('1' == $sendStatus) {
            return true;
        } else {
            return false;
        }
    }
}
