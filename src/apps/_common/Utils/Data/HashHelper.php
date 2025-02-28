<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/29
 * Time: 10:04
 */

namespace Common\Utils\Data;


class HashHelper
{
    public static $key = 'x7sdcGU3fj8m/tDCyvsBehwI19M1FcwvQqWuFpPoDHlFk=';

    /**
     * 加密函数
     * @param $data
     * @return string
     */
    public static function ticketEncrypt($data)
    {
        if(is_array($data)){
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        $iv = base64_encode(substr(md5(self::$key), 0, 8));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', base64_decode(self::$key), OPENSSL_RAW_DATA, base64_encode($iv));
        return base64_encode($encrypted);
    }

    //解密函数
    public static function ticketDecrypt($data)
    {
        $iv = base64_encode(substr(md5(self::$key), 0, 8));
        $encrypted = base64_decode($data);
        $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', base64_decode(self::$key), OPENSSL_RAW_DATA,
            base64_encode($iv));
        return $decrypted;
    }
}