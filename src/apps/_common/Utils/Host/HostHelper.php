<?php

namespace Common\Utils\Host;

use Zhuzhichao\IpLocationZh\Ip;

class HostHelper
{
    /**
     * 获取ip地址
     * @return mixed
     */
    public static function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        if (strpos($ip, ",") !== false) {
            $ip = explode(",", $ip)[0];
        }
        return $ip;
    }

    /**
     * ip地址定位
     * @param $ip
     * @return mixed
     * @suppress PhanUndeclaredClassMethod
     */
    public static function getAddressByIp($ip)
    {
        $ipAddressData = Ip::find($ip);
        if (empty($ipAddressData[2])) {
            return $ipAddressData[1];
        }
        return $ipAddressData[2];
    }

    /**
     * ip地址定位 返回英文
     * @param $ip
     * @return mixed
     * @suppress PhanUndeclaredClassMethod
     */
    public static function getEnAddressByIp($ip)
    {
        $ipAddressData = geoip($ip);
        # 解析不了
        if ($ipAddressData->default) {
            return '-';
        }
        return $ipAddressData->country . '-' . $ipAddressData->city;
    }

    public static function getDomain($httpType = false)
    {
        if ($httpType) {
            return self::getHttpType() . $_SERVER['HTTP_HOST'];
        }
        return 'https://' . $_SERVER['HTTP_HOST'];
    }

    public static function getHttpType()
    {
        return ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    }

    public static function isEN()
    {
        $locale = $_COOKIE['umi_locale'] ?? env('APP_LOCALE', 'en-US');
        return $locale == 'en-US';
    }
}
