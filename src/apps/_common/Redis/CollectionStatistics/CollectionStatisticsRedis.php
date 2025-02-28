<?php

namespace Common\Redis\CollectionStatistics;

use Carbon\Carbon;
use Common\Redis\BaseRedis;
use Common\Redis\RedisKey;
use Common\Utils\MerchantHelper;

/**
 * Class CollectionStatisticsRedis
 * 催收统计 redis 记录
 * @package Common\Redis\CollectionStatistics
 */
class CollectionStatisticsRedis
{
    use BaseRedis;

    /** 当日逾期订单计数 */
    const KEY_OVERDUE_COUNT = 'key_today_overdue_count';
    /** 当日逾期结清订单计数 */
    const KEY_OVERDUE_FINISH_COUNT = 'key_overdue_finish_count';
    /** 当日进入坏账计数 */
    const KEY_COLLECTION_BAD_COUNT = 'key_collection_bad_count';
    /** 当日承诺还款订单计数 */
    const KEY_PROMISE_PAID_COUNT = 'key_promise_paid_count';
    /** 当日催收次数统计 */
    const KEY_COLLECTION_COUNT = 'key_collection_count'; // 用催收人员 催收次数计算
    /** 催收人员list key & hash 计数 */
    const KEY_STAFF_HASH = 'key_staff_hash';

    /** 催收人员hash字段：当日分配订单计数 */
    const FIELD_STAFF_ALLOT_ORDER = 'staff_allot_order';
    /** 催收人员hash字段：当日承诺还款订单计数 */
    const FIELD_STAFF_PROMISE_PAID = 'staff_promise_paid';
    /** 催收人员hash字段：当日成功订单计数(流转 逾期结清) */
    const FIELD_STAFF_OVERDUE_FINISH = 'staff_overdue_finish';
    /** 催收人员hash字段：当日坏账订单计数(流转 已坏账) */
    const FIELD_STAFF_COLLECTION_BAD = 'staff_collection_bad';
    /** 催收人员hash字段：当日催收次数 */
    const FIELD_STAFF_COLLECTION_COUNT = 'staff_collection_count';

    /** 默认计数过期时间：暂定3天 */
    protected $defaultExpires = 259200;

    /**
     * 指定key自增，并设置默认过期时间
     * @param $key
     * @param $merchantId
     * @param $increment
     * @return mixed
     */
    public function incr($key, $merchantId = null, $increment = '1')
    {
        $key = $this->getKey($key, null, $merchantId);

        $incr = $this->redis::INCRBY($key, $increment);

        $this->redis::EXPIRE($key, $this->getDefaultExpires());

        return $incr;
    }

    /**
     * @param $key
     * @param $date
     * @param $merchantId
     * @return string
     */
    public function getKey($key, $date = null, $merchantId = null)
    {
        $date = $this->getDate($date);

        if (!$merchantId) {
            $merchantId = MerchantHelper::getMerchantId();
        }

        return RedisKey::COLLECTION_STATISTICS . $merchantId . ':' . $date . ':' . $key;
    }

    /**
     * @param $data
     * @return string
     */
    public function getDate($data = null)
    {
        return Carbon::parse($data)->toDateString();
    }

    protected function getDefaultExpires()
    {
        return $this->defaultExpires;
    }

    /**
     * 根据 key & date 获取计数值
     * @param $key
     * @param $date
     * @return string
     */
    public function get($key, $date = null)
    {
        return $this->redis::GET($this->getKey($key, $date)) ?? '0';
    }

    /**
     * 指定hash key自增，并设置默认过期时间
     * @param $staffId
     * @param $field
     * @param $merchantId
     * @param string $increment
     * @return mixed
     */
    public function hIncr($staffId, $field, $merchantId = null, $increment = '1')
    {
        $key = $this->getStaffHasKey($staffId, null, $merchantId);

        $hIncr = $this->redis::HINCRBY($key, $field, $increment);

        $this->redis::EXPIRE($key, $this->getDefaultExpires());

        //加入 staff list
        $this->sAddStaffList($staffId, $merchantId);

        return $hIncr;
    }

    /**
     * 根据 staffId & date 获取hash计数值
     * @param $staffId
     * @param $date
     * @return array
     */
    public function hGetAll($staffId, $date = null)
    {
        $data = [];
        if (is_array($staffId)) {
            foreach ($staffId as $id) {
                $data[$id] = $this->redis::HGETALL($this->getStaffHasKey($id, $date));
            }
        } else {
            $data = $this->redis::HGETALL($this->getStaffHasKey($staffId, $date));
        }
        return $data;
    }

    public function hGet($staffId, $field, $date = null)
    {
        $data = [];
        if (is_array($staffId)) {
            foreach ($staffId as $id) {
                $data[$id] = $this->redis::HGET($this->getStaffHasKey($id, $date), $field);
            }
        } else {
            $data = $this->redis::HGET($this->getStaffHasKey($staffId, $date), $field);
        }
        return $data;
    }

    /**
     * @param $staffId
     * @param $date
     * @param $merchantId
     * @return string
     */
    protected function getStaffHasKey($staffId, $date = null, $merchantId = null)
    {
        $key = self::KEY_STAFF_HASH . ':' . $staffId;

        return $this->getKey($key, $date, $merchantId);
    }

    /**
     * 添加催收人员列表(防止使用 keys)
     * @param $staffId
     * @param $merchantId
     * @return mixed
     */
    protected function sAddStaffList($staffId, $merchantId = null)
    {
        $key = $this->getKey(self::KEY_STAFF_HASH, null, $merchantId);
        $staffId = (array)$staffId;

        $this->redis::SADD($key, ...$staffId);
        $this->redis::EXPIRE($key, $this->getDefaultExpires());
        return true;
    }

    /**
     * 获取催收人员列表
     * @param $date
     * @return mixed
     */
    public function getStaffList($date = null)
    {
        return $this->redis::SMEMBERS($this->getKey(self::KEY_STAFF_HASH, $date));
    }

    /**
     * 统计 当日催收次数
     * @param $date
     * @return float|int
     */
    public function statisticsCollectCount($date = null)
    {
        $staffList = $this->getStaffList($date);

        $data = $this->hGet($staffList, self::FIELD_STAFF_COLLECTION_COUNT, $date);

        return array_sum($data);
    }

    public function resetStatistics()
    {
        $allS = $this->redis::KEYS(RedisKey::COLLECTION_STATISTICS . '*');

        foreach ($allS as $key) {
            $this->redis::DEL($key);
        }

        return true;
    }
}
