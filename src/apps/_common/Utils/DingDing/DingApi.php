<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/17
 * Time: 15:01
 */

namespace Common\Utils\DingDing;

use Common\Redis\Ding\DingAccessTokenRedis;
use GuzzleHttp\Client;

class DingApi
{
    /**
     * 获取钉钉persistentCode
     * @param $tmp_auth_code
     * @return mixed
     */
    public static function getPersistentCode($tmp_auth_code)
    {
        $access_token = self::getAccessToken();
        $url = "https://oapi.dingtalk.com/sns/get_persistent_code?access_token=" . $access_token;
        $client = new Client();
        $res = $client->request('POST', $url, [
            'body' => json_encode([
                'tmp_auth_code' => $tmp_auth_code
            ])
        ]);
        $body = json_decode($res->getBody());
        //access_token失效
        if ($body->errcode > 0) {
            DingAccessTokenRedis::redis()->del();
            self::getAccessToken();
        }
        return $body;
    }

    /**
     * 获取钉钉accessToken
     * @return mixed
     */
    public static function getAccessToken()
    {
        if (empty(DingAccessTokenRedis::redis()->exists())) {
            $url = "https://oapi.dingtalk.com/sns/gettoken?appid=" . config('config.ding_appid') . "&appsecret=" . config('config.ding_appsecret');
            $client = new Client();
            $res = $client->request('GET', $url);
            $body = json_decode($res->getBody());
            if ($body->errcode) {
                abort(500, $body->errmsg);
            }
            DingAccessTokenRedis::redis()->set($body->access_token, 7100);
        }
        return DingAccessTokenRedis::redis()->get();
    }

    /**
     * 获取钉钉snsToken
     * @param $persistent_code
     * @return mixed
     */
    public static function getSnsToken($persistent_code)
    {
        if (!isset($persistent_code->openid)) {
            return false;
        }
        $access_token = self::getAccessToken();
        $url = "https://oapi.dingtalk.com/sns/get_sns_token?access_token=" . $access_token;
        $client = new Client();
        $res = $client->request('POST', $url, [
            'body' => json_encode([
                'openid' => $persistent_code->openid,
                'persistent_code' => $persistent_code->persistent_code
            ])
        ]);
        $body = json_decode($res->getBody());
        return $body;
    }

    /**
     * 获取钉钉用户信息
     * @param $sns_token
     * @return mixed
     */
    public static function getDingUserInfo($sns_token)
    {
        $url = "https://oapi.dingtalk.com/sns/getuserinfo?sns_token=" . $sns_token->sns_token;
        $client = new Client();
        $res = $client->request('get', $url);
        $body = json_decode($res->getBody(), true);
        return $body;
    }

}
