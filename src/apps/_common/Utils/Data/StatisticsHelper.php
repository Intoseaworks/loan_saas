<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/25
 * Time: 11:03
 * @author ChangHai Zhan
 */

namespace Common\Utils\Data;

class StatisticsHelper
{
    /**
     * 同比计算
     *
     * @param $count1
     * @param $count2
     * @return float|int
     */
    public static function chainRatio($num1, $num2)
    {
        if ($num1 == 0 && $num2 == 0) {
            return 0;
        }
        if ($num1 == 0) {
            return -100;
        }
        if ($num2 == 0) {
            return 100;
        }
        return number_format(($num1 - $num2) / $num2 * 100, 2, '.', '') ?: 0;
    }

    /**
     * 比例
     *
     * @param $num1
     * @param $num2
     * @return float|int
     */
    public static function percent($num1, $num2)
    {
        if ($num1 == 0 && $num2 == 0) {
            return 0;
        }
        if ($num1 == 0) {
            return 0;
        }
        if ($num2 == 0) {
            return 100;
        }
        return number_format($num1 / $num2 * 100, 2);
    }

    public static function numberFormat($num, $decimals = 2)
    {
        return number_format($num, $decimals);
    }

    public static function hoursArr()
    {
        return ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
    }

    public static function dateArr($timeStart, $timeEnd)
    {
        $endDate = $timeEnd;
        $nextDate = $startDate = $timeStart;
        while (strtotime($endDate) >= strtotime($nextDate)) {
            $dateArr[] = $nextDate;
            $nextDate = DateHelper::addDays(1, 'Y-m-d', $nextDate);
        }
        return $dateArr;
    }
}
