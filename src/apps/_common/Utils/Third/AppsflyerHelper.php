<?php

namespace Common\Utils\Third;

use Common\Utils\Helper;
use Common\Models\Third\ThirdAfPushLog;

class AppsflyerHelper {

    use Helper;

    const CONFIG = [
        "host" => "https://api2.appsflyer.com/inappevent/",
        "dev_key" => "apnstmjnXZs3XNASX76p5a",
        "app_id" => [
            "2" => "com.SurityCash.hxov",
            "4" => "com.peranyo.cash.personal.loan.credit.peso.fast.lend.easy.quick.borrow.online.ph",
            "1" => "com.u5.e_perash",
            "3" => "com.lanhai.upesocash",
            "6" => "com.peralending.www",
        ],
    ];

    public function s2s($eventName, $eventValue, $merchantId, $userId) {
        if (!isset(self::CONFIG['app_id'][$merchantId])){
            return false;
        }
        $url = self::CONFIG['host'] . self::CONFIG['app_id'][$merchantId];
        $orderId = $eventValue['order_id'] ?? 0;
        $advertisingId = $this->guid();
        if($userAfInfo = \Common\Models\User\UserAfInfo::model()->where("user_id", $userId)->first()){
            $advertisingId = $userAfInfo->advertising_id ?? $advertisingId;
        }
        $data = [
            "appsflyer_id" => $this->guid(),
            "customer_user_id" => "customer_{$userId}",
            "advertising_id" => $advertisingId,
            "eventName" => $eventName,
            "eventValue" => json_encode($eventValue),
        ];
        $header = [
            "authentication: " . self::CONFIG['dev_key'],
            "Content-Type: application/json"
        ];
        echo $url;
        if(\Yunhan\Utils\Env::isProd()){
            $res = \Common\Utils\Curl::post(json_encode($data), $url, $header);
        }else{
            $res = "ok";
        }
        
        ThirdAfPushLog::model()->createModel([
            "merchant_id" => $merchantId,
            "order_id" => $orderId,
            "af_url" => $url,
            "result" => is_string($res) ? $res : json_encode($res),
        ]);
        if ($res == 'ok') {
            return true;
        }
        return false;
    }

    private function guid() {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            $uuid = chr(123)// "{"
                    . substr($charid, 0, 8) . $hyphen
                    . substr($charid, 8, 4) . $hyphen
                    . substr($charid, 12, 4) . $hyphen
                    . substr($charid, 16, 4) . $hyphen
                    . substr($charid, 20, 12)
                    . chr(125); // "}"
            return $uuid;
        }
    }

}
