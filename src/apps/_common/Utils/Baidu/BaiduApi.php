<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/26
 * Time: 17:48
 */

namespace Common\Utils\Baidu;


use Common\Utils\Curl;
use Common\Utils\DingDing\DingHelper;

class BaiduApi
{
    const BAIDU_APP_KEY = 'zyplQUIeGiOmmE37zEZCsluvwtFGiCBr';
    const BAIDU_TOKEN = 'V7Nd3DFoWZCYWphWIGHvWy3FPGNdI7ie';

    const URL_MAPS = 'http://api.map.baidu.com/reverse_geocoding/v3/';
    const URL_SHORT_URL = 'https://dwz.cn/admin/v2/create';

    /**
     * 百度短链 每月免费1w点
     * 短网址跳转一次消耗1个点。创建一条一年有效的短网址消耗10个点，创建一条永久有效的短网址耗20个点
     * @param $url
     * @param string $expireTime | long-term | 1-year
     */
    public static function getShortUrl($url, $expireTime = '1-year')
    {
        $bodys = array('Url' => $url, 'TermOfValidity' => $expireTime);
        $headers = array('Content-Type:application/json', 'Token:' . self::BAIDU_TOKEN);
        $result = json_decode(Curl::post(json_encode($bodys), self::URL_SHORT_URL, $headers), true);
        if (array_get($result, 'Code') < 0) {
            DingHelper::notice($result, '百度短链异常');
        }
        return array_get($result, 'ShortUrl');
    }

    /**
     * 百度地图 - 逆地理编码服务 每天30w免费调用量
     * @param $longitude
     * @param $latitude
     * @return mixed
     */
    public static function getAddress($latitude, $longitude)
    {
        $ak = self::BAIDU_APP_KEY;
        # lat<纬度>,lng<经度>
        $position = $latitude . ',' . $longitude;
        $url = self::URL_MAPS . "?ak={$ak}&output=json&coordtype=wgs84ll&location={$position}";
        $data = json_decode(Curl::get($url), true);
        $addressComponent = array_get($data, 'result.addressComponent', []);
        $addressComponent['address'] = array_get($data, 'result.formatted_address', '') . array_get($data,
                'result.business', '');
        return $addressComponent;
        /**
         * 第三方返回示例
         * {
         * status: 0,
         * result: {
         * location: {
         * lng: 113.96504702353795,
         * lat: 22.532213825141326
         * },
         * formatted_address: "广东省深圳市南山区",
         * business: "科技园,华侨城,白石洲",
         * addressComponent: {
         * country: "中国",
         * country_code: 0,
         * country_code_iso: "CHN",
         * country_code_iso2: "CN",
         * province: "广东省",
         * city: "深圳市",
         * city_level: 2,
         * district: "南山区",
         * town: "",
         * adcode: "440305",
         * street: "",
         * street_number: "",
         * direction: "",
         * distance: ""
         * },
         * pois: [ ],
         * roads: [ ],
         * poiRegions: [ ],
         * sematic_description: "",
         * cityCode: 340
         * }
         * }
         */
    }
}
