<?php

namespace Common\Redis\Approve;

use Common\Models\Config\Config;
use Common\Redis\BaseRedis;
use Common\Redis\RedisKey;

class ApproveRedis
{
    use BaseRedis;

    /**
     * @param $adminId
     * @param $orderIds
     * @param bool $refreshExpire
     * @param $expire
     * @return mixed
     */
    public function sadd($adminId, $orderIds, bool $refreshExpire = true, $expire = null)
    {
        if (!$orderIds) {
            return 0;
        }

        $count = $this->redis::sadd($this->getKey($adminId), ...$orderIds);

        if ($refreshExpire) {
            $this->refreshSExpire($adminId, $expire);
        }
        return $count;
    }

    /**
     * @param $adminId
     * @return string
     */
    public function getKey($adminId = '')
    {
        return RedisKey::APPROVE_MANUAL_SET . $adminId;
    }

    /**
     * 获取过期时间
     * @param null $expire
     *
     * @return float|int|null
     */
    protected function getApproveManualExpire($expire = null)
    {
        if (!is_null($expire) && is_numeric($expire)) {
            return $expire;
        }

        return Config::getApproveManualOvertime() * 60;
    }

    /**
     * @param $adminId
     * @param bool $flatten
     * @return mixed
     */
    public function getSByAdminId($adminId = null, bool $flatten = true)
    {
        if (is_null($adminId)) {
            $keys = $this->redis::keys($this->getKey('*'));
            $data = [];
            foreach ($keys as $key) {
                $data[$key] = $this->redis::smembers($key);
            }
            return $flatten ? array_flatten($data) : $data;
        }

        return $this->redis::smembers($this->getKey($adminId));
    }

    /**
     * 根据值获取对应admin_id
     * @param $values
     * @return mixed
     */
    public function getAdminIdByValue($values = null)
    {
        $set = $this->getSByAdminId(null, false);

        $allotArr = [];
        foreach ($set as $key => $arr) {
            $adminId = str_after($key, $this->getKey());
            foreach ($arr as $item) {
                if (!is_null($values)) {
                    in_array($item, $values) && $allotArr[$item] = $adminId;
                } else {
                    $allotArr[$item] = $adminId;
                }
            }
        }

        return $allotArr;
    }

    /**
     * 获取并删除一个随机元素
     * @param $adminId
     * @return mixed
     */
    public function spop($adminId)
    {
        return $this->redis::spop($this->getKey($adminId));
    }

    /**
     * 根据 admin_id 随机获取set成员
     * @param $adminId
     * @return \Illuminate\Support\Facades\Redis
     */
    public function srandmember($adminId)
    {
        return $this->redis::srandmember($this->getKey($adminId));
    }

    /**
     * 判断成员是否存在
     * @param $adminId
     * @param $orderId
     * @return mixed
     */
    public function sismember($adminId, $orderId)
    {
        return $this->redis::sismember($this->getKey($adminId), $orderId);
    }

    /**
     * 刷新过期时间
     * @param $adminId
     * @param $expire
     * @return mixed
     */
    public function refreshSExpire($adminId, $expire = null)
    {
        return $this->redis::expire($this->getKey($adminId), $this->getApproveManualExpire($expire));
    }

    /**
     * 从指定集合中删除value
     * @param $adminId
     * @param $orderId
     * @return mixed
     */
    public function delS($adminId, $orderId)
    {
        $orderId = (array)$orderId;
        return $this->redis::srem($this->getKey($adminId), ...$orderId);
    }

    /**
     * @param $adminId
     * @return mixed
     */
    public function del($adminId)
    {
        return $this->redis::del($this->getKey($adminId));
    }
}
