<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 11:14
 */

namespace Common\Redis\Services;

use Illuminate\Support\Facades\Redis;

class ServicesRedis
{
    protected $redis;

    public function __construct()
    {
        $this->redis = Redis::connection('services');
    }

    public static function redis()
    {
        return new static();
    }

    /**
     * 判断是否命中krazybee多头
     * @param $telephone
     * @return bool|string
     */
    public function isHitKb($telephone)
    {
        // common:register:手机:app名称
        $key = 'common:register:' . $telephone . ':krazybee';

        $data = $this->redis->get($key);

        if (is_null($data)) {
            return false;
        }

        $data = json_decode($data, true);

        return isset($data['is_register']) ? (bool)$data['is_register'] : false;
    }
}
