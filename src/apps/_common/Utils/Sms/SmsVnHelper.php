<?php

/**
 * Created by PhpStorm.
 * User: Nio
 * Date: 2020/06/01
 * Time: 10:45
 */

namespace Common\Utils\Sms;

use Common\Utils\Curl;
use Common\Utils\Helper;

class SmsVnHelper {

    use Helper;

    public static function send($mobile, $eventId, $values) {
        $url = config('config-vn.sms_url');
        $appType = [
            "0" => [], //0延时发送
            "1" => ['096DA8', '28761F', "219851", "B64AAD", "8BDFFB", "E96D29", "8F3BF7", "65A8AF", "E84A90", "ACCFCD", "75B170", '82A0BC', "38FF80", "9568B5", "832770", "E81544", "C794B3", "A4C440", "C036EA", "502570", "C32259"], //实时
            "2" => ['4560A8',], //2验证码
            "3" => [], //3营销
            "4" => ['0C9706', 'D5C1EC', "7ED21F", "605EBB", "73F3B2", "B49B8B", "0BC681", "4AE9B5", "4FCFCD", "8E7CD9", "4AD075"], //4语音验证码
        ];
        $remark = [
            "验证码" => ["4560A8", "0C9706"],
            "审批状态通知" => ["096DA8", "D5C1EC", "28761F", "219851", "B64AAD", "82A0BC", "C32259", "4AD075"],
        ];
        $message = t($eventId, "sms-vn");
        foreach ($values as $k => $v) {
            $message = str_replace("{{" . $k . "}}", $v, $message);
        }
        $data = [
            "appCode" => config('config-vn.sms_app_code'),
            "appType" => self::getEventType($eventId, $appType) ?? 1,
            "message" => $message,
            "areaCode" => config('config-vn.area_code'),
            "phone" => $mobile,
            "remark" => self::getEventType($eventId, $remark) ?? "催收",
        ];
        $res = json_decode(Curl::post($data, $url), true);
        if ('0' == $res['code']) {
            return true;
        } else {
            throw new \Exception($res['msg']);
        }
    }

    public static function getEventType($id, $list) {
        foreach ($list as $key => $item) {
            if (in_array($id, $item)) {
                return $key;
            }
        }
        return "";
    }

}
