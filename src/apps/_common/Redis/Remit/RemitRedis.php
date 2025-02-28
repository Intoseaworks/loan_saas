<?php

namespace Common\Redis\Remit;

use Common\Redis\BaseRedis;
use Common\Redis\RedisKey;

class RemitRedis
{
    use BaseRedis;

    //默认过期时间 5分钟
    protected $defaultExpire = '300';

    public function set($orderId, $adminId, $expire = null)
    {
        return $this->redis::set($this->getKey($orderId), $adminId, 'EX', $this->getManualRemitExpire($expire));
    }

    /**
     * @param $orderId
     * @return string
     */
    public function getKey($orderId = '')
    {
        return RedisKey::REMIT_MANUAL_LOCK . $orderId;
    }

    /**
     * 获取过期时间
     * @param null $expire
     * @return string|null
     */
    protected function getManualRemitExpire($expire = null)
    {
        if (!is_null($expire) && is_numeric($expire)) {
            return $expire;
        }

        return $this->defaultExpire;
    }

    /**
     * 获取所有进入出款状态的订单
     * @return array
     */
    public function getAllLockOrderId()
    {
        return array_keys(RemitRedis::redis()->getByOrderId());
    }

    /**
     * @param $orderId
     * @return mixed
     */
    public function getByOrderId($orderId = null)
    {
        if (is_null($orderId)) {
            $keys = $this->redis::keys($this->getKey('*'));
            $keyPrefix = $this->getKey();
            $data = [];
            foreach ($keys as $key) {
                $orderId = str_after($key, $keyPrefix);
                $data[$orderId] = $this->redis::get($key);
            }
            return $data;
        }

        return $this->redis::get($this->getKey($orderId));
    }

    public function exists($orderId)
    {
        return $this->redis::exists($this->getKey($orderId));
    }

    /**
     * 刷新过期时间
     * @param $orderId
     * @param $expire
     * @return mixed
     */
    public function refreshExpire($orderId, $expire = null)
    {
        return $this->redis::expire($this->getKey($orderId), $this->getManualRemitExpire($expire));
    }

    /**
     * @param $orderId
     * @return mixed
     */
    public function del($orderId)
    {
        return $this->redis::del($this->getKey($orderId));
    }
}
