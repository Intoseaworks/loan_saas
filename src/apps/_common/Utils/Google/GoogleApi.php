<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/5
 * Time: 19:21
 */

namespace Common\Utils\Google;

use JMD\Utils\HttpHelper;

class GoogleApi
{
    const URL_GOOGLE_MAPS = 'https://maps.googleapis.com/maps/api/geocode/json';

    public static function getAddress($position)
    {
        $key = 'AIzaSyAJv-DAA94KfRG8048hAVQ7ZmicwbBzThc';
        $url = self::URL_GOOGLE_MAPS . "?latlng={$position}&key={$key}";
        $googleAddress = json_decode(HttpHelper::curl($url), true);
        if (!$googleAddress || !isset($googleAddress['results'][0]['address_components'])) {
            return false;
        }
        $city = array_get($googleAddress, 'results.0.address_components.2.long_name', '');
        $address = array_get($googleAddress, 'results.0.formatted_address', '');
        return ['city' => $city, 'address' => $address];
    }

}
