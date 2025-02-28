<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-12
 * Time: 15:20
 */

namespace Common\Utils;


use Common\Utils\Email\EmailHelper;
use JMD\Utils\SignHelper;
use Yunhan\Utils\Env;

class RequestHelper
{
    /**
     * @param $url
     * @param $params
     * @param $secret
     * @return bool|string
     * @throws \Exception
     */
    public static function curlEncryptPost($url, $params, $secret)
    {
        $except = static::handleExcept($params);
        $params['sign'] = SignHelper::sign($except, $secret);
        $params = array_merge($except, $params);
        return static::curlPost($url, $params);
    }

    /**
     * @param $params
     * @return mixed
     */
    public static function handleExcept($params)
    {
        // 不参与签名的key,(数据有时候可能比较大,签名会影响性能)
        if (isset($params['_except'])) {
            if (is_array($params['_except'])) {
                foreach ($params['_except'] as $item) {
                    unset($params[$item]);
                }
            } else {
                unset($params[$params['_except']]);
            }
        }

        return $params;
    }

    /**
     * @param $url
     * @param $params
     * @return bool|string
     */
    public static function curlPost($url, $params)
    {
        $client = new \GuzzleHttp\Client();
        $options = [
            'form_params' => $params,
        ];

        // xdebug  调试
        if (Env::isDev()) {
            if (strpos($url, '?') === false) {
                $url .= '?XDEBUG_SESSION_START=PHPSTORM';
            } else {
                $url .= '&XDEBUG_SESSION_START=PHPSTORM';
            }
        }

        if (strpos($url, 'https://') === 0) {
            $options['verify'] = false;
        }
        try {
            $res = $client->post($url, $options);
        } catch (\Exception $e) {
            EmailHelper::send($e, '[审批系统 curl请求出错]');
            return false;
        }
        if ($res->getStatusCode() != 200) {
            return false;
        }
        return json_decode((string)$res->getBody(), true);
    }
}
