<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/17
 * Time: 15:01
 */

namespace Common\Utils\DingDing;

class DingData
{
    /**
     * 根据code获取钉钉用户信息
     * @param $code
     * @return mixed
     */
    public static function getDingUserInfoByCode($code)
    {
        $persistent_code = DingApi::getPersistentCode($code);
        $sns_token = DingApi::getSnsToken($persistent_code);
        if (!$sns_token) {
            return false;
        }
        return DingApi::getDingUserInfo($sns_token);
    }
}
