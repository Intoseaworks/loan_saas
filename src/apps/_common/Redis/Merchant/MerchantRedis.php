<?php

namespace Common\Redis\Merchant;

use Common\Redis\BaseRedis;
use Common\Redis\RedisKey;

class MerchantRedis
{
    use BaseRedis;

    public function getByMerchantId($merchantId)
    {
        return $this->redis::SMEMBERS($this->getKey($merchantId));
    }

    protected function getKey($key)
    {
        return RedisKey::MERCHANT_APP_BELONG . $key;
    }

    public function addAppToMerchant($merchantId, $appId)
    {
        $appId = (array)$appId;

        $res = $this->redis::SADD($this->getKey($merchantId), ...$appId);
        $this->redis::EXPIRE($this->getKey($merchantId), 86400);

        return $res;
    }

    public function clear($merchantId)
    {
        return $this->redis::DEL($this->getKey($merchantId));
    }
}
