<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/25
 * Time: 11:57
 */

namespace Common\Utils\Sense\lib;

class SenseAuthorization
{
    public function getSenceAuth()
    {
        $config = $this->getConfig();
        $API_KEY = config('cashnow.sense.api_key');
        $API_SECRET = config('cashnow.sense.api_secret');
        //生成nonce
        $nonce = $this->makeNonce(16);
        //生成unix 时间戳timestamp
        $timestamp = (string)time();
        //将timestamp、nonce、API_KEY 这三个字符串进行升序排列（依据字符串首位字符的ASCII码)，并join成一个字符串stringSignature
        $stringSignature = $this->makeStringSignature($nonce, $timestamp, $API_KEY);
        //对stringSignature和API_SECRET做hamc-sha256 签名，生成signature
        $signature = $this->signString($stringSignature, $API_SECRET);
        //将签名认证字符串赋值给HTTP HEADER 的 Authorization 中
        $Authorization = "key=" . $API_KEY . ",timestamp=" . $timestamp . ",nonce=" . $nonce . ",signature=" . $signature;

        $header = array(
            'Authorization: ' . $Authorization
        );
        return $header;
    }

    private function getConfig()
    {
        return config('cashnow.sense');
    }

    public function makeNonce($length)
    {
        // 生成随机 nonce。位数可以自己定
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $nonce = '';
        for ($i = 0; $i < $length; $i++) {
            $nonce .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $nonce;
    }

    public function makeStringSignature($nonce, $timestamp, $API_KEY)
    {
        //将timestamp、nonce、API_KEY 这三个字符串进行升序排列（依据字符串首位字符的ASCII码)，并join成一个字符串
        $payload = array(
            'API_KEY' => $API_KEY,
            'nonce' => $nonce,
            'timestamp' => $timestamp
        );
        //对首字母排序
        sort($payload);
        //join到一个字符串
        $signature = join($payload);
        return $signature;
    }

    public function signString($string_to_sign, $API_SECRET)
    {
        //对两个字符串做hamc-sha256 签名
        return hash_hmac("sha256", $string_to_sign, $API_SECRET);
    }
}
