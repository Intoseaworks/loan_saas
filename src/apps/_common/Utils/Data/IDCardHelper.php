<?php

namespace Common\Utils\Data;

use Common\Utils\Email\EmailHelper;

/**
 * 身份证验证相关
 * Class IdCardHelper
 * @package common\helpers
 */
Class IDCardHelper
{
    // 身份证过期错误码
    const VALIDITY_CODE = 99;

    /**
     * 验证身份证有效期
     * @param $startDate
     * @param $endDate
     * @return bool|\Exception
     */
    public static function checkValidity($startDate, $endDate)
    {
        try {
            // 判断时间格式是否正确
            $startDate = self::formatDate($startDate);
            if (!$startDate) {
                throw new \Exception('开始日期格式不正确');
            }

            // 格式化有效期开始时间，方便对比
            $startDate = strtotime($startDate);

            // 有效期到期时间为中文的时候
            if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $endDate)) {
                // 有效期结束时间 特殊字段
                if (!in_array($endDate, ['长期'])) {
                    return false;
                }
                // 有效期开始时间不能大于现在的时间
                if ($startDate >= time()) {
                    throw new \Exception('开始日期不能大于现在的时间');
                }
                return true;
            }


            // 判断时间格式是否正确
            $endDate = self::formatDate($endDate);
            if (!$endDate) {
                throw new \Exception('结束日期格式不正确');
            }
            // 格式化有效期结束时间
            $endDate = strtotime($endDate);
            // 判断是否过了有效期
            if ($endDate <= time()) {
                throw new \Exception('证件已过期', self::VALIDITY_CODE);
            }
            // 有效期开始时间不能大于有效期结束时间
            if ($startDate >= $endDate) {
                throw new \Exception("开始时间不能大于结束时间");
            }
            // 有效期开始时间和结束时间都不能大于现在的时间
            if ($startDate >= time()) {
                throw new \Exception("开始时间和不能大于现在的时间");
            }

            return true;
        } catch (\Exception $exception) {
            EmailHelper::sendException($exception);
            return false;
        }
    }

    /**
     * 验证身份证号码是否正确
     * @param $idCard
     */
    public static function checkIdCard($idCard)
    {

    }

    /**
     * 格式化时间 .不支持strtotime
     * @param $date
     * @return mixed
     * @throws \Exception
     */
    private static function formatDate($date)
    {
        // 替换特殊字符串
        $date = preg_replace('/[ |.|\/|-]/', '/', $date);

        // 判断年份、月份、日、是否正确
        $inStr = ['.', '/', '-'];
        foreach ($inStr as $symbol) {
            if (strstr($date, $symbol)) {
                if (!self::checkDate($symbol, $date)) {
                    return false;
                }
            }
        }
        if (!strtotime($date)) {
            return false;
        }
        return $date;
    }

    /**
     * 日期字符串检验
     * @param $symbol
     * @param $date
     * @return bool
     * @throws \Exception
     */
    private static function checkDate($symbol, $date)
    {
        if (strstr($date, $symbol)) {
            $dateArr = explode($symbol, $date);
            if (count($dateArr) != 3) {
                throw new \Exception("年-月-日格式不正确");
            }
            $dateArr1 = $dateArr[0];
            $dateArr2 = $dateArr[1];
            $dateArr3 = $dateArr[2];
            // 年份四位
            if (strlen($dateArr1) != 4) {
                throw new \Exception("年份格式不正确【错误信息：您输入的是{$dateArr1}】");
            }
            // 月份不能小于0或者大于12月
            if ((int)$dateArr2 <= 0 || (int)$dateArr2 > 12) {
                throw new \Exception("月份格式<=0月或者>12月");
            }
            // 天数不能超31
            if ((int)$dateArr3 <= 0 || (int)$dateArr3 > 31) {
                throw new \Exception("日期格式>31天或者<=0天");
            }
            return true;
        }
        return false;
    }
}