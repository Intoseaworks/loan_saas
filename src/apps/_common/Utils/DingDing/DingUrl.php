<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 16:53
 */

namespace Common\Utils\DingDing;

class DingUrl
{
    /**
     * 拼接生成goto地址
     * @return string
     */
    public static function getGotoUrl()
    {
        return urlencode('https://oapi.dingtalk.com/connect/qrconnect?appid=' . config('config.ding_appid') . '&response_type=code&scope=snsapi_login&state=STATE&redirect_uri=' . self::getHttpHost() . config('config.ding_login_callback'));
    }

    /**
     * 拼接生成ding地址
     * @return string
     */
    public static function getDingUrl()
    {
        return 'https://oapi.dingtalk.com/connect/oauth2/sns_authorize?appid=' . config('config.ding_appid') . '&response_type=code&scope=snsapi_login&state=STATE&redirect_uri=' . self::getHttpHost() . config('config.ding_login_callback');
    }

    public static function getHttpHost()
    {
        $httpType = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $httpHost = $_SERVER['HTTP_HOST'] ?? 'tests';
        $redirect_url = $httpType . $httpHost . '/';
        return $redirect_url;
    }
}