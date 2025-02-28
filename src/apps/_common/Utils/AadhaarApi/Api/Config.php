<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-03
 * Time: 17:35
 */

namespace Common\Utils\AadhaarApi\Api;

class Config
{
    /**
     * pan card
     *
     * @var string
     */
    const PAN_CARD = '/pan';

    /**
     * 驾驶证
     *
     * @var string
     */
    const DRIVING_LICENCE = '/verify-dl';

    /**
     * aadhaar
     *
     * @var string
     */
    const BANK_CARD = '/verify-bank';

    /**
     * voter id
     *
     * @var string
     */
    const VOTER_ID = '/verify-voter';

    /**
     * passport
     *
     * @var string
     */
    const PASSPORT = '/verify-passport';

    /**
     * Esign
     */
    const ESIGN = '/gateway/esign/init';

    /**
     * @return string
     */
    public static function getPanCardUrl()
    {
        return self::getConfig('base_url') . static::PAN_CARD;
    }

    /**
     * @param $name
     * @return mixed
     */
    public static function getConfig($name)
    {
        return config('cashnow.aadhaar_api_config.' . $name);
    }

    /**
     * @return string
     */
    public static function getDrivingLicenceUrl()
    {
        return self::getConfig('base_url') . static::DRIVING_LICENCE;
    }

    /**
     * @return string
     */
    public static function getBankCardUrl()
    {
        return self::getConfig('base_url') . static::BANK_CARD;
    }

    /**
     * @return string
     */
    public static function getVoterIdUrl()
    {
        return self::getConfig('base_url') . static::VOTER_ID;
    }

    /**
     * @return string
     */
    public static function getPassportUrl()
    {
        return self::getConfig('base_url') . static::PASSPORT;
    }

    /**
     * @return string
     */
    public static function getApiKey()
    {
        return self::getConfig('api_key');
    }

    /**
     * @return string
     */
    public static function getAgencyId()
    {
        return self::getConfig('agency_id');
    }

    /**
     * @return string
     */
    public static function getEsignUrl()
    {
        return self::getConfig('base_url') . static::ESIGN;
    }
}
