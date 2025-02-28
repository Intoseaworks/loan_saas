<?php

namespace Common\Cookie\BindDing;

use Illuminate\Support\Facades\Cookie;

class BingDingCookie
{
    /**
     * 过期时间
     */
    const CACHE_EXPIRE = 3600;

    /**
     * @return string
     */
    private static function getKey()
    {
        return 'ding';
    }

    public static function set($value, $expireTime = 0)
    {
        setcookie(self::getKey(), $value, $expireTime, "/", config('config.cookie_domain'));
    }

    public static function get()
    {
        return Cookie::get(self::getKey());
    }
}
