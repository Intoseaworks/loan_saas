<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/6/13
 * Time: 15:31
 * @author ChangHai Zhan
 */

namespace Common\Redis\Ding;

use Common\Redis\BaseRedis;
use Common\Redis\RedisKey;

/**
 * Class DemoRedis
 * @author ChangHai Zhan
 */
class DingAccessTokenRedis
{
    use BaseRedis;

    public $tokenName = 'access_token';

    /**
     * @param $value
     * @param $expireTime
     * @return mixed
     */
    public function set($value, $expireTime = 3600 * 24 * 365)
    {
        return $this->redis::set($this->getKey(), $value, 'EX', $expireTime);
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return RedisKey::DING_ACCESS_TOKEN . $this->tokenName;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->redis::get($this->getKey());
    }

    /**
     * @return mixed
     */
    public function del()
    {
        return $this->redis::del($this->getKey());
    }

    public function exists()
    {
        return $this->redis::exists($this->getKey());
    }
}
