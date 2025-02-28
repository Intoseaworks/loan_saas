<?php

namespace Common\Utils\Data;

/**
 * Created by PhpStorm.
 * User: Windysay
 * Date: 2017/3/22
 * Time: 18:01
 */
Class MoneyHelper
{
    public static function formatMoneyOut($value, $zeroReset = true, $resetStr = '---')
    {
        if ($zeroReset && $value == 0) {
            return $resetStr;
        }
        return self::round2point($value);
    }

    /**
     * 四舍五入保留两位
     * @param $value
     * @param $decimals
     * @return string
     */
    public static function round2point($value, int $decimals = 2)
    {
        return number_format((float)$value, $decimals, '.', '');
    }

    /**
     * 比较两个数
     * @param $left
     * @param $right
     * @param int $scale
     * @return int
     */
    public static function compare($left, $right, $scale = 2)
    {
        return bccomp($left, $right, $scale);
    }

    public static function add($left, $right, $scale = 2)
    {
        return bcadd($left, $right, $scale);
    }

    public static function sub($left, $right, $scale = 2)
    {
        return bcsub($left, $right, $scale);
    }

    /**
     * 千分位字符串转数字
     * @param $str
     * @return bool|float
     */
    public static function thousandsToNumber($str)
    {
        $str = trim($str);

        // '888,882,947.788,812,3'  888,882,947   888,888.12  888  888.88
        if (!preg_match('/^(\d|\d,\d)+(\.(\d|\d,\d)+)?$/', $str, $match)) {
            return false;
        }

        return (float)str_replace(',', '', $str);
    }

    /**
     * 分转元 保留二位小数
     * @param $money
     * @return string
     */
    public static function branch_to_yuan($money)
    {
        if (is_numeric($money)) {
            return sprintf("%.2f", $money / 100);
        }
        return '';
    }

    /**
     * 数字金额转换成中文大写金额的函数
     * String Int $num 要转换的小写数字或小写字符串
     * return 大写字母
     * 小数位为两位
     * @param $num
     * @return string
     */
    public static function num_to_rmb($num)
    {
        $c1 = "零壹贰叁肆伍陆柒捌玖";
        $c2 = "分角圆拾佰仟万拾佰仟亿";
        //精确到分后面就不要了，所以只留两个小数位
        $num = round($num, 2);
        //将数字转化为整数
        $num = $num * 100;
        $num = (string)$num;
        if (strlen($num) > 10) {
            return "金额太大，请检查";
        }
        $i = 0;
        $c = "";
        while (1) {
            if ($i == 0) {
                //获取最后一位数字
                $n = substr($num, strlen($num) - 1, 1);
            } else {
                $n = $num % 10;
            }
            //每次将最后一位数字转化为中文
            $p1 = substr($c1, 3 * $n, 3);
            $p2 = substr($c2, 3 * $i, 3);
            if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '圆'))) {
                $c = $p1 . $p2 . $c;
            } else {
                $c = $p1 . $c;
            }
            $i = $i + 1;
            //去掉数字最后一位了
            $num = $num / 10;
            $num = (int)$num;
            //结束循环
            if ($num == 0) {
                break;
            }
        }
        $j = 0;
        $slen = strlen($c);
        while ($j < $slen) {
            //utf8一个汉字相当3个字符
            $m = substr($c, $j, 6);
            //处理数字中很多0的情况,每次循环去掉一个汉字“零”
            if ($m == '零圆' || $m == '零万' || $m == '零亿' || $m == '零零') {
                $left = substr($c, 0, $j);
                $right = substr($c, $j + 3);
                $c = $left . $right;
                $j = $j - 3;
                $slen = $slen - 3;
            }
            $j = $j + 3;
        }
        //这个是为了去掉类似23.0中最后一个“零”字
        if (substr($c, strlen($c) - 3, 3) == '零') {
            $c = substr($c, 0, strlen($c) - 3);
        }
        //将处理的汉字加上“整”
        if (empty($c)) {
            return "零圆整";
        } else {
            if (is_numeric($num)) {
                return $c;
            }
            return $c . "整";
        }
    }
}
