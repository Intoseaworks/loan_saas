<?php
/**
 * Created by IntelliJ IDEA.
 * User: Win10
 * Date: 2021/7/30
 * Time: 18:47
 */

namespace Common\Utils;

/**




Class ShareCodeUtils





邀请码生成器，基本原理




1）参数用户ID




2）使用自定义进制转换之后为：V




3）最小code长度为6位，若不足则在后面添加分隔字符'F'：VF




4）在VF后面再随机补足4位，得到形如 VFAADD




5）反向转换时以'F'为分界线，'F'后面的不再解析

 */

class ShareCodeHelper {


// 32个进制字符（0,1 没加入，容易和 o l 混淆，O 未加入，F 未加入，用于补位）

// 顺序可进行调整, 增加反推难度

    private static $base = ['H', 'V', 'E', '8', 'S', '2', 'D', 'Z', 'X', '9', 'C', '7', 'P','5', 'I', 'K', '3', 'M', 'J', 'U', 'A', 'R', '4', 'W', 'Y', 'L', 'T', 'N', '6', 'B', 'G', 'Q'];


// F为补位字符，不能和上述字符重复

    private static $pad = "F";


// 进制长度

    private static $decimal_len = 32;


// 生成code最小长度

    private static $code_min_len = 6;


    /**



    id转为code

    相除去模法


    @param $id

    @return string

     */

    public static function idToCode($id)

    {

        $result = "";
         while (floor($id / static::$decimal_len) > 0){

                $index = $id % static::$decimal_len;

                $result.= static::$base[$index];

                $id = floor($id / static::$decimal_len);

        }

        $index =  $id % static::$decimal_len;

        $result.= static::$base[$index];

        // code长度不足,则随机补全

        $code_len = strlen($result);

        if ($code_len < static::$code_min_len) {

                $result .= static::$pad;

                for ($i = 0; $i < static::$code_min_len - $code_len - 1; $i ++) {

                            $result .= static::$base[rand(0, static::$decimal_len -1)];

                }

        }

        return $result;

}


    /**



    code转为id

    根据code获取对应的下标

    在进行进制转换

    eg: N8FASR, F为分隔符, 后面不在处理

    N ---> 27

    8 ---> 3

    进制转换 2732(0) + 332(1) = 123

    32(0) ---> 32的0次方

    32(1) ---> 32的1次方


    @param $code

    @return string

     */

    public static function codeToId($code)

    {

        $result = 0;

        $base_flip_map = array_flip(static::$base);

        $is_pad = strpos($code, static::$pad);
         if (!empty($is_pad)) {

                $len_real = $is_pad;

            } else {

                $len_real = strlen($code);

        }

        for ($i = 0; $i < $len_real; $i ++) {

            $str = $code[$i];

            $index = $base_flip_map[$str] ?? '';

            if ($index === '') {

                break;

            }

            $result += pow(static::$decimal_len, $i) * $index;

        }

        return $result;
}
}
//$num = "123";
//
//var_dump(ShareCodeUtils::idToCode($num));
//$code = "N8FMJ3";
//
//var_dump(ShareCodeUtils::codeToId($code));
