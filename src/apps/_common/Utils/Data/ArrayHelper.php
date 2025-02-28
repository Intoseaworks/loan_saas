<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/25
 * Time: 11:03
 * @author ChangHai Zhan
 */

namespace Common\Utils\Data;

class ArrayHelper
{
    /**
     * 键值转前端二维
     *
     * @param $arr
     * @param string $keyName
     * @param string $valName
     * @return array
     */
    public static function arrToOption($arr, $keyName = 'value', $valName = 'label')
    {
        $data = [];
        foreach ($arr as $key => $val) {
            $data[] = [
                $keyName => $key,
                $valName => $val,
            ];
        }
        return $data;
    }

    /**
     * 多维键值转前端二维
     *
     * @param $arrs
     * @param string $keyName
     * @param string $valName
     * @return array
     */
    public static function arrsToOption($arrs, $keyName = 'value', $valName = 'label')
    {
        $data = [];
        foreach ($arrs as $key => $arr) {
            $data[$key] = self::arrToOption($arr, $keyName, $valName);
        }
        return $data;
    }

    /**
     * 把数组指定值作为数组key
     * @param $arr
     * @param $key
     * @return array
     */
    public static function arrayChangeKey($arr, $key)
    {
        $processedArr = array();
        if (is_array($arr) && !empty($arr)) {
            foreach ($arr as $item) {
                $processedArr[$item[$key]] = $item;
            }
            /*$collection = collect($arr);
            $keyed = $collection->keyBy($key);
            $processedArr = $keyed->all();*/
        }
        return $processedArr;
    }

    /**
     * 值改为键值（键为值）
     *
     * @param $arr
     * @return array
     */
    public static function valToKeyVal($arr)
    {
        $data = [];
        foreach ($arr as $val) {
            $data[$val] = $val;
        }
        return $data;
    }

    /**
     * 判断数组每个键对应的值都不为空
     * @param array $arr
     * @return bool
     */
    public static function allValuesNotEmpty(array $arr)
    {
        return count($arr) == count(array_filter($arr));
    }

    /**
     * 将数组转字符串
     * @param $data
     * @return false|string
     * @suppress PhanTypeMismatchReturn
     */
    public static function arrayToJson($data)
    {
        if (is_array($data)) {
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        return $data;
    }

    /**
     * 将数组转字符串
     * @param $str
     * @return mixed
     */
    public static function jsonToArray($str)
    {
        if (is_array($str)) {
            return $str;
        }
        if (is_null(json_decode((string)$str))) {
            return $str;
        }
        return json_decode((string)$str, true);
    }

    /**
     * 姓名比对
     *
     * @param $val1
     * @param $val2
     * @return bool
     */
    public static function stringIntersect($val1, $val2)
    {
        $arr1 = array_map('strtolower', array_map('trim', explode(' ', $val1)));
        $arr2 = array_map('strtolower', array_map('trim', explode(' ', $val2)));

        # 姓名首位模糊匹配 first name前面有部分一致
        $firstName1 = array_get($arr1, '0');
        $firstName2 = array_get($arr2, '0');
        if ($firstName1 && $firstName2 && (strpos($firstName1, $firstName2) === 0 || strpos($firstName2, $firstName1) === 0)) {
            return true;
        }

        # 单词匹配
        $result = array_intersect($arr1, $arr2);
        return $result ? true : false;
    }

    /**
     * 银行卡全段交叉比对
     *
     * @param $fullname
     * @param $bankname
     * @return bool
     */
    public static function stringIntersectByBank1($fullname, $bankname)
    {
        $fullnameArr = array_map('strtolower', array_map('trim', explode(' ', $fullname)));
        $bankname = strtolower(str_replace(' ', '', $bankname));
        foreach ($fullnameArr as $key => $name) {
            if (strlen($name) <= 2) {
                continue;
            } else {
                if (strpos($bankname, $name)
                    || strpos($name, $bankname)
                    || $bankname == $name
                    || strpos($name, $bankname) === 0
                    || strpos($bankname, $name) === 0) {
                    return true;
                }
            }
        }
        echo "{$fullname} --- {$bankname}<br>";
        return false;
    }

    /**
     * fullname多段比对银行卡bankname, 加风控定制逻辑
     *
     * @param $fullname
     * @param $bankname
     * @return bool
     */
    public static function stringIntersectByBank2($fullname, $bankname)
    {
        $bankname = preg_replace('/^(Mr|Mrs|Miss|Master)\s{1,3}/', "", $bankname);
        if ($fullname == $bankname) {
            return true;
        }
        $fullnameArr = array_map('strtolower', array_map('trim', explode(' ', $fullname)));
        $fullnameCount = count($fullnameArr);//fullname段数
        $successCount = 0;//比对成功的段数
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
            if (strlen($name) <= 2 || $bankname=='') {
                continue;
            } else {
                if (strpos($bankname, $name)
                    || strpos($name, $bankname)
                    || $bankname == $name
                    || strpos($name, $bankname) === 0
                    || strpos($bankname, $name) === 0) {
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
     * fullname多段比对银行卡bankname
     * @param $fullname
     * @param $bankname
     * @return bool
     */
    public static function stringIntersectByBank3($fullname, $bankname)
    {
        $fullnameArr = array_map('strtolower', array_map('trim', explode(' ', $fullname)));
        $banknameArr = array_map('strtolower', array_map('trim', explode(' ', $bankname)));
        foreach ($fullnameArr as $fullnameKey => $fullnameVal) {
            if (strlen($fullnameVal) <= 2) {
                continue;
            }
            foreach ($banknameArr as $banknameKey => $banknameVal) {
                if (strlen($banknameVal) <= 2) {
                    continue;
                } else {
                    if (strpos($banknameVal, $fullnameVal)
                        || strpos($fullnameVal, $banknameVal)
                        || $banknameVal == $fullnameVal
                        || strpos($banknameVal, $fullnameVal) === 0
                        || strpos($fullnameVal, $banknameVal) === 0
                    ) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public static function numericToInteger(array &$arr)
    {
        foreach ($arr as &$item) {
            if (is_array($item)) {
                self::numericToInteger($item);
            } elseif (filter_var($item, FILTER_VALIDATE_INT)) {
                $item = filter_var($item, FILTER_VALIDATE_INT);
            }
        }
    }

    /**
     * 去除多维数组中的空值
     * @param array $arr 目标数组
     * @param array $values 去除的值  默认 去除  '',null,false,0,'0',[]
     * @return mixed
     * @author
     */
    public static function filterArray($arr, $values = ['', null, false, 0, '0', []])
    {
        foreach ($arr as $k => $v) {
            if (is_array($v) && count($v) > 0) {
                $arr[$k] = self::filterArray($v, $values);
            }
            foreach ($values as $value) {
                if ($v === $value) {
                    unset($arr[$k]);
                    break;
                }
            }
        }
        return $arr;
    }

    /**
     * 二维数组根据某些字段去重
     * @param $arr
     * @param null $field
     * @return array
     */
    public static function arrayUnique($arr, $field = null)
    {
        $res = [];
        if (!is_null($field)) {
            $field = (array)$field;
        }
        foreach ($arr as $item) {
            ksort($item); // 关键，保证key的顺序

            if (is_array($item)) {
                if (is_null($field)) {
                    $kv = $item;
                } else {
                    $kv = array_only($item, $field);
                }
                //md5 防止字段内容过长、特殊字符等
                $k = md5(implode('_', $kv));
                $res[$k] = $item;
            } else {
                $res[] = $item;
            }
        }

        return array_values($res);
    }

    /**
     * 判断是否为二维数组
     * @param $array
     * @return bool
     */
    public static function isTwoDimension($array)
    {
        return count($array) == count($array, 1) ? false : true;
    }

    public static function isAssocArray(array $var)
    {
        return array_diff_assoc(array_keys($var), range(0, sizeof($var))) ? TRUE : FALSE;
    }

    /**
     * 一维数组转换成二维数组
     * @param $arr
     * @return array|null
     */
    public static function convertTwoDimensional($arr)
    {
        if (empty($arr) || !is_array($arr)) {
            return null;
        }

        if (!self::isTwoDimension($arr)) {
            return [$arr];
        }

        return $arr;
    }
}
