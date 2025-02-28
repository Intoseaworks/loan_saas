<?php

namespace Risk\Common\Redis\Lock;

use Common\Redis\RedisKey;

class LockRedis extends \Common\Redis\Lock\LockRedis
{
    public function getKey($key)
    {
        return RedisKey::RISK_LOCK . $key;
    }
}
