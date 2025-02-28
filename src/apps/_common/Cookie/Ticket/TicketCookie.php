<?php

namespace Common\Cookie\Ticket;

use Illuminate\Support\Facades\Cookie;

class TicketCookie
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
        return 'ticket_saas';
    }

    public static function set($value, $expireTime = 0)
    {
        setcookie(self::getKey(), $value, $expireTime, "/", config('config.cookie_domain'));
    }

    public static function get()
    {
        return Cookie::get(self::getKey());
    }

    public static function del()
    {
        setcookie(self::getKey(), '', time() - config('config.cookie_expire'), "/", config('config.cookie_domain'));
    }

    /**
     * @param $ticket
     * @param float|int $expire
     * @return mixed
     */
    public static function make($ticket, $expire = 0)
    {
        if ($expire == 0) {
            $expire = config('config.cookie_expire');
        }
        return Cookie::make(self::getKey(), $ticket, intval($expire / 60), "/", config('config.cookie_domain'));
    }
}
