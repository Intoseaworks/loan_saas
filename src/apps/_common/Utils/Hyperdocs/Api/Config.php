<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-03
 * Time: 17:35
 */

namespace Common\Utils\Hyperdocs\Api;

class Config
{

    /**
     * @var string
     */
    const READ_PAN = '/readPAN';

    /**
     * @var string
     */
    const READ_KYC = '/readKYC';

    /**
     * @var string
     */
    const READ_AADHAAR = '/readAadhaar';

    /**
     * @var string
     */
    const READ_PASSPORT = '/readPassport';

    /**
     * @var string
     */
    const READ_VOTER_ID = '/readVoterID';

    /**
     * @var string
     */
    const READ_DRIVING = '/readDL';

    /**
     * @return string
     */
    public static function getReadKYCUrl()
    {
        return self::getConfig('base_url') . static::READ_KYC;
    }

    public static function getConfig($name)
    {
        return config('cashnow.hyperdocs_api_config.' . $name);
    }

    /**
     * @return string
     */
    public static function getReadAadhaarUrl()
    {
        return self::getConfig('base_url') . static::READ_AADHAAR;
    }

    /**
     * @return string
     */
    public static function getReadPassportUrl()
    {
        return self::getConfig('base_url') . static::READ_PASSPORT;
    }

    /**
     * @return string
     */
    public static function getReadVoterIdUrl()
    {
        return self::getConfig('base_url') . static::READ_VOTER_ID;
    }

    /**
     * @return string
     */
    public static function getPanCardUrl()
    {
        return self::getConfig('base_url') . static::READ_PAN;
    }

    /**
     * @return string
     */
    public static function getReadDrivingUrl()
    {
        return self::getConfig('base_url');
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
