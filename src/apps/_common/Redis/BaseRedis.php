<?php

namespace Common\Redis;

use Illuminate\Support\Facades\Redis;

/**
 * Trait KeyRedis
 * @property Redis $redis
 * @package Common\Common\Model
 */
trait BaseRedis
{
    public $_redis;

    protected static $_classRedis;

    /**
     * @param string $classRedis
     * @return static
     */
    public static function redis($classRedis = null)
    {
        if (is_null($classRedis)) {
            $classRedis = static::class;
        }
        if (!isset(self::$_classRedis[$classRedis])) {
            self::$_classRedis[$classRedis] = new $classRedis();
        }
        return self::$_classRedis[$classRedis];
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        $name = 'get' . ucfirst($name);
        return $this->$name();
    }

    /**
     * @return string
     */
    public function getRedis()
    {
        return Redis::class;
    }
}
