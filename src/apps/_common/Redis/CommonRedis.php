<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 11:14
 */

namespace Common\Redis;

class CommonRedis
{
    use BaseRedis;

    /**
     * @param $ticket
     * @param $value
     * @param $expireTime
     * @return mixed
     */
    public function set($key, $value, $expireTime = 3600 * 24)
    {
        return $this->redis::set($this->getKey($key), $value, 'EX', $expireTime);
    }

    /**
     * @param $ticket
     * @return string
     */
    public function getKey($key)
    {
        return RedisKey::COMMON . $key;
    }

    /**
     * @param $ticket
     * @return mixed
     */
    public function get($key)
    {
        return $this->redis::get($this->getKey($key));
    }

    /**
     * @param $ticket
     * @return mixed
     */
    public function del($key)
    {
        return $this->redis::del($this->getKey($key));
    }

    /**
     * @param $key
     * @return mixed
     */
    public function incr($key)
    {
        return $this->redis::incr($this->getKey($key));
    }

    public function expire($key, $value)
    {
        return $this->redis::expire($this->getKey($key), $value);
    }

    /**
     * @param $db
     * @return mixed
     */
    public function select($db)
    {
        return $this->redis::command('select', [$db]);
    }

    /**
     * 公共每日计数
     */
    public function verifyCount($redisKey, $incr = true)
    {
        $redisKey = 'verify_count:'.$redisKey;
        $count = CommonRedis::redis()->get($redisKey);
        if ($incr) {
            CommonRedis::redis()->incr($redisKey);
        }
        if (is_null($count)) {
            CommonRedis::redis()->expire($redisKey, strtotime(date('Y-m-d', strtotime('+1 day'))) - time());
        }
        return $count ?: 0;
    }
}
