<?php

namespace Common\Redis\Risk;

use Common\Redis\BaseRedis;
use Common\Redis\RedisKey;

class RiskSendTaskDataRedis
{
    use BaseRedis;

    //默认过期时间 12小时
    protected $defaultExpire = '43200';

    // 大于等于3移除 = 尝试三次 0 1 2
    protected $maxRetry = 3;

    /**
     * @param $key
     * @return string
     */
    public function getKey($key)
    {
        return RedisKey::RISK_SEND_TASK_DATA . $key;
    }

    /**
     * 根据类型存储
     * 某一类型超过指定次数时剔除，返回剩余未超次数的类型
     * @param $key
     * @param $type
     * @param int $score
     * @return array
     */
    public function add($key, $type, $score = 1)
    {
        $type = (array)$type;
        $redisKey = $this->getKey($key);

        $rem = [];
        foreach ($type as $item) {
            $res = $this->redis::ZINCRBY($redisKey, $score, $item);
            if ($res >= $this->maxRetry) {
                $rem[] = $item;
            }
        }
        if ($rem) {
            $this->redis::ZREM($redisKey, ...$rem);
        }

        $surplusTypes = $this->redis::ZRANGE($redisKey, 0, -1);

        $this->refreshExpire($key);

        return array_intersect($type, $surplusTypes);
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
     * 刷新过期时间
     * @param $key
     * @param $expire
     * @return mixed
     */
    public function refreshExpire($key, $expire = null)
    {
        return $this->redis::expire($this->getKey($key), $this->getManualRemitExpire($expire));
    }

    /**
     * @param $key
     * @return mixed
     */
    public function del($key)
    {
        return $this->redis::del($this->getKey($key));
    }
}
