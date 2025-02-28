<?php


namespace Common\Utils;


class ValidatorHelper
{
    /**
     * 菲律宾手机号码
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function mobile($data = null)
    {
        $_pattern_in = "/^[0]\d{10}$/";
        return self::_regex($_pattern_in, $data);
    }

    /**
     * 匹配正则公共方法
     * @param $pattern string 匹配模式
     * @param $subject string 对象
     * @return bool
     */
    private static function _regex($pattern, $subject = null)
    {
        if ($subject === null) {
            return false;
        }
        if (preg_match($pattern, $subject)) {
            return true;
        }
        return false;
    }

    /**
     * 印度固话号码
     * 1.不加国际号时，0开头，根据城市区号不同，最少10，最多12；
     * 2.加国际号时，9开头，根据城市区号不同，最少11，最多13；
     * @param $data mixed 数字
     * @return bool
     */
    public static function telephone($data = null)
    {
        $_pattern_in = "/^(\+?9|0)?[0]\d{9,11}$/";
        return self::_regex($_pattern_in, $data);
    }

    /**
     * Email
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function email($data = null)
    {
        $_res = filter_var($data, FILTER_VALIDATE_EMAIL);
        if (empty($_res)) {
            return false;
        }
        /** MX检测 */
        $path = explode('@', $data);
        $host = end($path);
        //MX邮箱域检测
        return checkdnsrr($host, "MX");
    }

    /**
     * 邮编
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function postcode($data = null)
    {
        $_pattern = "/^[1-9]\d{5}(?!\d)$/";
        return self::_regex($_pattern, $data);
    }

    /**
     * 中文
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function zh($data = null)
    {
        $_pattern = "/^[\x{4e00}-\x{9fa5}]+$/u";
        return self::_regex($_pattern, $data);
    }

    /**
     * 印度
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function india($data = null)
    {
        /** 不能空格开头 */
        if (substr($data, 0) == ' ') {
            return false;
        }
        $_pattern = "/^[ 0-9A-Za-z]+$/";
        return self::_regex($_pattern, $data);
    }

    /**
     * URL地址
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function url($data = null)
    {
        $_res = filter_var($data, FILTER_VALIDATE_URL);
        return empty($_res) ? false : true;
    }

    /**
     * 印度银行卡
     *
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function bankCard($data = null)
    {
        $_pattern = "/^\d{6,20}$/";
        return self::_regex($_pattern, $data);
    }

    /**
     * IPv4
     * @param $data mixed 数字或者字符串
     * @return bool
     **/
    public static function ip($data = null)
    {
        $_res = filter_var($data, FILTER_VALIDATE_IP);
        return empty($_res) ? false : true;
    }

    public static function validNICNum($data = null){
        $_pattern = "/^\d{14}$/";
        return self::_regex($_pattern, $data);
    }
    
    public static function validPassportCard($data = null){
        if($data!=''){
            return true;
        }
        return false;
    }
}
