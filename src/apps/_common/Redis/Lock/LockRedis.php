<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 11:14
 */

namespace Common\Redis\Lock;

use Common\Redis\BaseRedis;
use Common\Redis\RedisKey;

class LockRedis
{
    use BaseRedis;

    public function set($key, $second = 10, $value = 'lock')
    {
        return $this->redis::set($this->getKey($key), $value, 'EX', $second);
    }

    public function getKey($key)
    {
        return RedisKey::API_LOCK . $key;
    }

    public function get($key)
    {
        return $this->redis::get($this->getKey($key));
    }

    public function del($key)
    {
        return $this->redis::del($this->getKey($key));
    }

    /**
     * @param $redisKey
     * @param int $second
     * @return bool
     */
    public function getLock($redisKey, $second = 1)
    {
        return $this->setLock($redisKey, $second);
    }

    /**
     * @param $redisKey
     * @param int $second
     * @return bool
     */
    public function setLock($redisKey, $second = 1)
    {
        return $this->redis::set($redisKey, 1, 'EX', $second, 'NX');
    }
}
