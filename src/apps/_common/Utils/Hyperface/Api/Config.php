<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-03
 * Time: 17:35
 */

namespace Common\Utils\Hyperface\Api;

class Config
{

    /**
     * @var string
     */
    const FACE = '/photo/verifyPair';


    /**
     * @return string
     */
    public static function getFaceUrl()
    {
        return self::getConfig('base_url') . static::FACE;
    }

    public static function getConfig($name)
    {
        return config('cashnow.hyperface_api_config.' . $name);
    }

    /**
     * @return string
     */
    public static function getAppId()
    {
        return self::getConfig('app_id');
    }

    /**
     * @return string
     */
    public static function getAppKey()
    {
        return self::getConfig('app_key');
    }

}
