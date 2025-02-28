<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-11
 * Time: 12:15
 */

namespace Common\Traits;

/**
 * @phan-file-suppress PhanUndeclaredMethod, PhanTypeMismatchArgumentInternal, PhanUndeclaredProperty
 * Trait GetInstance
 * @package Common\Traits
 */
trait GetInstance
{
    /**
     * @var array
     */
    private static $_instances = [];

    /**
     * @return static
     */
    public static function getInstance()
    {
        $params = func_get_args();
        $class = get_called_class();

        if ($params) {
            $md5 = md5(json_encode($params));
            if (empty(static::$_instances[$class][$md5])) {
                static::$_instances[$class][$md5] = new static(...$params);
            }

            return static::$_instances[$class][$md5];
        } else {
            if (empty(static::$_instances[$class][0])) {
                static::$_instances[$class][0] = new static();
            }

            return static::$_instances[$class][0];
        }
    }
}
