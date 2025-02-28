<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/25
 * Time: 11:03
 * @author ChangHai Zhan
 */

namespace Common\Utils\Data;

use Illuminate\Support\Str;

class StringHelper {

    public static function trans($arr, $keyName = 'key', $valName = 'val') {
        $data = [];
        foreach ($arr as $key => $val) {
            $data[] = [
                $keyName => $key,
                $valName => $val
            ];
        }
        return $data;
    }

    /**
     * 删除空格
     * @param $str
     * @return mixed
     */
    public static function delSpace($str = '') {
        $before = array(" ", "　", "\t", "\n", "\r", "<br>", "<br/>");
        $after = array("", "", "", "", "", "", "");
        return str_replace($before, $after, $str);
    }

    /**
     * 多空格转空格
     *
     * @param $str
     */
    public static function spacesToSpace($str) {
        return preg_replace("# {2,}#", ' ', trim($str));
    }

    /**
     * 生成不带'-'的uuid
     * @suppress PhanUndeclaredClassMethod
     * @return mixed
     */
    public static function generateUuid() {
        return str_replace('-', '', Str::uuid());
    }

    /**
     * 格式化手机号码 去除+91 - ' '等
     * @param $telephone
     * @return mixed
     */
    public static function formatTelephone($telephone) {
        /** 剔除特殊字符 */
        $before = array(" ", "　", "+63", "-", '(', ')', '-', '+',"+95");
        $after = array("", "", "", "", "", "", "", "", "", "");
        $telephone = str_replace($before, $after, $telephone);
        return $telephone;
    }

    public static function maskIdCardNo($str, $startLength = 4, $endLength = 4, $maskLength = 4, $mask = '*') {
        return static::maskNo($str, $startLength, $endLength, $maskLength, $mask);
    }

    public static function maskBankCardNo($str, $startLength = 4, $endLength = 4, $maskLength = 4, $mask = '*') {
        return static::maskNo($str, $startLength, $endLength, $maskLength, $mask);
    }

    public static function maskTelephone($str, $startLength = 3, $endLength = 4, $maskLength = 4, $mask = '*') {
        return static::maskNo($str, $startLength, $endLength, $maskLength, $mask);
    }

    public static function maskFullname($str, $startLength = 1, $endLength = 0, $mask = '*') {
        return static::maskNo($str, $startLength, $endLength, mb_strlen($str) - $startLength, $mask);
    }

    public static function maskNo($str, $startLength = 4, $endLength = 4, $maskLength = 4, $mask = '*') {
        if (empty($str)) {
            return $str;
        }
        $maskStrs = '';
        for ($i = 0; $i < $maskLength; $i++) {
            $maskStrs .= $mask;
        }

        return mb_substr($str, 0, $startLength) . $maskStrs . mb_substr($str, -$endLength, $endLength);
    }

    /**
     * 验证码数字转英文
     *
     * @param $num
     * @return string
     */
    public static function numToEn($num) {
        $enNumberArr = ['Zero', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
        $enNumber = '';
        $num = str_split($num);
        for ($i = 0; $i < count($num); $i++) {
            $enNumber .= $enNumberArr[$num[$i]] . ' ';
        }
        return $enNumber;
    }

    public static function xmlParser($str) {
        $xmlParser = xml_parser_create();
        if (!xml_parse($xmlParser, $str, true)) {
            xml_parser_free($xmlParser);
            return false;
        } else {
            xml_parser_free($xmlParser); // 释放 XML 解析器
            libxml_disable_entity_loader(true); //禁止引用外部xml实体
            // LIBXML_NOCDATA表示不对CDATA进行转意，而是把他当成普通的文本进行解析
            return (json_decode(json_encode(simplexml_load_string($str, 'SimpleXMLElement', LIBXML_NOCDATA)), true));
        }
    }

    /**
     * 字符串处理：去空格转小写
     * @param $str
     * @return string|array
     */
    public static function clearSpaceToLower($str) {
        if (is_array($str)) {
            foreach ($str as &$i) {
                $i = strtolower(str_replace(' ', '', $i));
            }
            return $str;
        }
        return strtolower(str_replace(' ', '', $str));
    }

    public static function nameExplode($name) {
        $nameArr = explode(' ', $name);
        $firstName = $nameArr[0];
        $middleName = isset($nameArr[1]) ? $nameArr[1] : "";
        $lastName = isset($nameArr[2]) ? $nameArr[2] : "";
        return [$firstName, $middleName, $lastName];
    }

    /**
     * fullname多段比对银行卡bankname, 加风控定制逻辑
     *
     * @param $fullname
     * @param $bankname
     * @return bool
     */
    public static function stringIntersectByBank2($fullname, $bankname) {
        $bankname = preg_replace('/^(Mr|Mrs|Miss|Master)\s{1,3}/', "", $bankname);
        if ($fullname == $bankname) {
            return true;
        }
        $fullnameArr = array_map('strtolower', array_map('trim', explode(' ', $fullname)));
        $fullnameCount = count($fullnameArr); //fullname段数
        $successCount = 0; //比对成功的段数
        $bankname = strtolower(str_replace(' ', '', $bankname));

        $max = 0;
        $secMax = 0;

        # 获取比对成功的段数
        foreach ($fullnameArr as $key => $name) {

            # 取fullanme第一第二长的名字段长度之和
            if (strlen($fullnameArr[$key]) > $max) {//自定义的$max和数组每个值比较，if比它大就将$max值赋给$secMax，把$arr[$i]赋给自己
                $secMax = $max;
                $max = strlen($fullnameArr[$key]);
            } else if (strlen($fullnameArr[$key]) <= $max && strlen($fullnameArr[$key]) > $secMax) {
                $secMax = strlen($fullnameArr[$key]);
            }
            # 长度小于等于2的名字段跳过
            if (strlen($name) <= 2) {
                continue;
            } else {
                if (strpos($bankname, $name) || strpos($name, $bankname) || $bankname == $name || strpos($name, $bankname) === 0 || strpos($bankname, $name) === 0) {
                    $successCount++;
                }
            }
        }

        # 根据fullname段数和比对成功段数判断是否通过
        if ($fullnameCount >= 3) {
            if ($successCount >= 2) {
                return true;
            }
            # 如果bankname长度小于等于fullanme第一第二长的名字段长度之和，规则调松
            if (strlen($bankname) <= $secMax + $max) {
                if ($successCount >= 1) {
                    //$fullname = strtolower($fullname);
                    //echo "{$fullname} --- {$bankname}".PHP_EOL.PHP_EOL;
                    return true;
                }
            }
        }
        if ($fullnameCount == 2) {
            if ($successCount >= 1) {
                return true;
            }
        }
        if ($fullnameCount == 1) {
            if ($successCount >= 1) {
                return true;
            }
        }

        //输出比对
        //$fullname = strtolower($fullname);
        //echo "{$fullname} --- {$bankname}".PHP_EOL.PHP_EOL;
        return false;
    }

    /**
     * 字符串脱敏
     * @param type $string
     * @param type $start
     * @param type $length
     * @param type $re
     * @return boolean
     */
    public static function desensitization($string, $start = 3, $length = 4, $re = '*') {
        if (empty($string)) {
            return false;
        }
        $strarr = array();
        $mb_strlen = mb_strlen($string);
        while ($mb_strlen) {//循环把字符串变为数组
            $strarr[] = mb_substr($string, 0, 1, 'utf8');
            $string = mb_substr($string, 1, $mb_strlen, 'utf8');
            $mb_strlen = mb_strlen($string);
        }
        $strlen = count($strarr);
        $begin = $start >= 0 ? $start : ($strlen - abs($start));
        $end = $last = $strlen - 1;
        if ($length > 0) {
            $end = $begin + $length - 1;
        } elseif ($length < 0) {
            $end -= abs($length);
        }
        for ($i = $begin; $i <= $end; $i++) {
            $strarr[$i] = $re;
        }
        if ($begin >= $end || $begin >= $last || $end > $last)
            return false;
        return implode('', $strarr);
    }

    /**
     * 十进制数转换成多进制
     * @param integer $num
     * @return string
     */
    public static function from10ToMu($num) {
        $dict = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $to = strlen($dict);
        $ret = '';
        $num *= 277094664;
        do {
            $ret = $dict[bcmod($num, $to)] . $ret;
            $num = bcdiv($num, $to);
        } while ($num > 0);
        return $ret;
    }

    /**
     * 多进制数转换成十进制数
     *
     * @param string $num
     * @return string
     */
    public static function fromMuTo10($num) {
        $num = strval($num);
        $dict = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $from = strlen($dict);
        $len = strlen($num);
        $dec = 0;
        for ($i = 0; $i < $len; $i++) {
            $pos = strpos($dict, $num[$i]);
            $dec = bcadd(bcmul(bcpow($from, $len - $i - 1), $pos), $dec);
        }
//        return $dec;
        return $dec/277094664;
    }

}
