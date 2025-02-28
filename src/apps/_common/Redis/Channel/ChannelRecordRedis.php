<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/2/14
 * Time: 14:52
 */

namespace Common\Redis\Channel;

use Carbon\Carbon;
use Common\Redis\BaseRedis;
use Common\Redis\RedisKey;

class ChannelRecordRedis
{
    use BaseRedis;

    /** 默认计数过期时间：暂定3天 */
    protected $defaultExpires = 259200;

    /**
     * @param $key
     * @param $date
     * @return string
     */
    public function getValue($key, $date = null)
    {
        $date = $this->getDate($date);

        return $this->redis::get(RedisKey::CHANNEL_COUNT_PREFIX . $date . ':' . $key);
    }

    /**
     * @param $data
     * @return string
     */
    public function getDate($data = null)
    {
        return Carbon::parse($data)->toDateString();
    }

    /**
     * @param $id
     * @param $event
     * @return mixed
     */
    public function record($id, $event)
    {
        return $this->incr($id . ':' . $event);
    }

    /**
     * 指定key自增，并设置默认过期时间
     * @param $key
     * @param $increment
     * @return mixed
     */
    public function incr($key, $increment = '1')
    {
        $key = $this->getKey($key);

        $incr = $this->redis::INCRBY($key, $increment);

        $this->redis::EXPIRE($key, $this->defaultExpires);

        return $incr;
    }

    /**
     * @param $key
     * @param $date
     * @return string
     */
    public function getKey($key, $date = null)
    {
        $date = $this->getDate($date);

        return RedisKey::CHANNEL_COUNT_PREFIX . $date . ':' . $key;
    }


}