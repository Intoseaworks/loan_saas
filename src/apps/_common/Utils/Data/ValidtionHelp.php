<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/3/10
 * Time: 11:37
 */

namespace Common\Utils\Data;


class ValidtionHelp
{
    /**
     * Luhn算法校验银行卡
     * @param $no
     * @return bool
     */
    static function validBankCardNo($no)
    {
        if (!is_numeric($no)) {
            return false;
        }
        $arr_no = str_split($no);
        $last_n = $arr_no[count($arr_no) - 1];
        krsort($arr_no);
        $i = 1;
        $total = 0;
        foreach ($arr_no as $n) {
            if ($i % 2 == 0) {
                $ix = $n * 2;
                if ($ix >= 10) {
                    $nx = 1 + ($ix % 10);
                    $total += $nx;
                } else {
                    $total += $ix;
                }
            } else {
                $total += $n;
            }
            $i++;
        }
        $total -= $last_n;
        $total *= 9;
        return $last_n == ($total % 10);
    }
}