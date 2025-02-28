<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 11:14
 */

namespace Common\Redis\Staff;

use Common\Redis\BaseRedis;
use Common\Redis\RedisKey;

class StaffIdRedis
{
    use BaseRedis;

    /**
     * @param $id
     * @return string
     */
    public function getKey($id)
    {
        return RedisKey::STAFF_ID . $id;
    }

    /**
     * @param $id
     * @param $ticket
     * @param float|int $expireTime
     * @return mixed
     */
    public function set($id, $ticket, $expireTime = 3600 * 24 * 365)
    {
        return $this->redis::set($this->getKey($id), $ticket, 'EX', $expireTime);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->redis::get($this->getKey($id));
    }

    /**
     * @param $id
     * @return mixed
     */
    public function del($id)
    {
        return $this->redis::del($this->getKey($id));
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
