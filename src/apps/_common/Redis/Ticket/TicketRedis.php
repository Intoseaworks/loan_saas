<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 11:14
 */

namespace Common\Redis\Ticket;

use Common\Models\Config\Config;
use Common\Redis\BaseRedis;
use Common\Redis\RedisKey;

class TicketRedis
{
    use BaseRedis;

    /**
     * @param $ticket
     * @return string
     */
    public function getKey($ticket)
    {
        return RedisKey::STAFF_TICKET . $ticket;
    }

    public function getExpireTime()
    {
        return Config::getValueByKey(Config::KEY_LOGIN_EXPIRE_HOURS);
    }

    /**
     * @param $ticket
     * @param $value
     * @param $expireTime
     * @return mixed
     */
    public function set($ticket, $value, $expireTime = 3600 * 24)
    {
        if(!$expireTime = $this->getExpireTime()){
            $expireTime = 8;
        }
        $expireTime = 3600*$expireTime;
        return $this->redis::set($this->getKey($ticket), $value, 'EX', $expireTime);
    }

    /**
     * @param $ticket
     * @return mixed
     */
    public function get($ticket)
    {
        return $this->redis::get($this->getKey($ticket));
    }

    /**
     * @param $ticket
     * @return mixed
     */
    public function del($ticket)
    {
        return $this->redis::del($this->getKey($ticket));
    }

    /**
     * @param $db
     * @return mixed
     */
    public function select($db)
    {
        return $this->redis::command('select', [$db]);
    }
}
