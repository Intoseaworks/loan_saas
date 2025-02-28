<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/25
 * Time: 11:03
 * @author ChangHai Zhan
 */

namespace Common\Utils\Data;

use Carbon\Carbon;

class DateHelper
{
    /**
     * @param $startTime
     * @param null $endTime
     * @return string
     */
    public static function usedDays($startTime, $endTime = null)
    {
        if ($endTime === null) {
            $endTime = time();
        }
        $times = $endTime - $startTime;

        $formatter = function ($data) {
            return sprintf('%02d', (string)$data);
        };

        $result = '00:00:00';
        if ($times > 0) {
            $hour = $formatter(floor($times / 3600));
            $minute = $formatter(floor(($times - 3600 * $hour) / 60));
            $second = $formatter($times - 3600 * $hour - 60 * $minute);
            $days = 0;
            if ($hour > 24) {
                $days = $formatter(floor($hour / 24));
                $hour = $formatter($hour % 24);
            }

            if (app('translator')->getLocale() == 'en-US') {
                $result = "{$days}d {$hour}h {$minute}m {$second}s";
            } else {
                $result = "{$days}天{$hour}小时{$minute}分钟{$second}秒";
            }
        }

        return $result;
    }

    public static function dateTime()
    {
        return Carbon::now()->toDateTimeString();
    }

    public static function date()
    {
        return Carbon::now()->toDateString();
    }


    public static function formatToDate($dataTime, $format = '')
    {
        if (!$dataTime) {
            return $dataTime;
        }
        # 日月年识别
        $dataTime = str_replace('/', '-', $dataTime);
        $date = ($dataTime != 0 && $dataTime) ? Carbon::parse($dataTime)->toDateString() : $dataTime;
        if ($format) {
            return self::format($date, $format);
        }
        return $date;
    }

    public static function formatToDateTime($dataTime, $format = '')
    {
        if (!$dataTime) {
            return $dataTime;
        }
        # 日月年识别
        $dataTime = str_replace('/', '-', $dataTime);
        $date = ($dataTime != 0 && $dataTime) ? Carbon::parse($dataTime)->toDateTimeString() : $dataTime;
        if ($format) {
            return self::format($date, $format);
        }
        return $date;
    }

    public static function timeToDateTime($time)
    {
        return ($time != 0 && $time) ? Carbon::parse($time)->toDateTimeString() : $time;
    }

    /**
     * 日期转毫秒时间戳
     * @param $formatTime
     * @return false|int
     */
    public static function ms($formatTime = '')
    {
        if ($formatTime == '') {
            return Carbon::now()->timestamp * 1000;
        }
        return strtotime($formatTime) * 1000;
    }

    /**
     * 毫秒时间戳转日期
     *
     * @param $ms
     * @param string $format
     * @return false|string
     */
    public static function msToDate($ms, $format = 'Y-m-d')
    {
        return !empty($ms) ? date($format, $ms / 1000) : '';
    }

    /**
     * 毫秒时间戳转日期时间
     *
     * @param $ms
     * @param string $format
     * @return false|string
     */
    public static function msToDateTime($ms, $format = 'Y-m-d H:i:s')
    {
        return !empty($ms) ? date($format, $ms / 1000) : '';
    }

    /**
     * 取两个时间的最小值
     *
     * @param $dt1
     * @param $dt2
     * @return Carbon
     */
    public static function min($dt1, $dt2)
    {
        $dt1 = Carbon::parse($dt1);
        $dt2 = Carbon::parse($dt2);
        return $dt1->min($dt2);
    }

    public static function diffInDays($date1, $date2 = '', $absolute = true)
    {
        if ($date2 == '') {
            $date2 = self::date();
        }
        # 需要取整
        $dt1 = Carbon::parse(DateHelper::formatToDate($date1));
        $dt2 = Carbon::parse(DateHelper::formatToDate($date2));
        return $dt1->diffInDays($dt2, $absolute);
    }

    public static function betweenInDays($date1, $date2, $date = '')
    {
        if ($date == '') {
            $date = date('Y-m-d H:i:s');
        }
        $date1 = self::formatToDate($date1, 'Y-m-d H:i:s');
        $date2 = self::formatToDate($date2, 'Y-m-d H:i:s');
        $dt1 = Carbon::parse($date1);
        $dt2 = Carbon::parse($date2);
        return Carbon::parse($date)->between($dt1, $dt2);
    }

    /**
     * 日期解析
     *
     * @param $time
     * @return string
     */
    public static function parseDate($time)
    {
        return Carbon::parse($time)->toDateString();
    }

    /**
     * 日期解析
     *
     * @param $time
     * @return string
     */
    public static function parseDateTime($time)
    {
        //$time yesterday -1 weeks
        return Carbon::parse($time)->toDateTimeString();
    }

    /**
     * 取当前Y-m-d前n天的Y-m-d
     *
     * @param $day
     * @param string $date
     * @return string
     */
    public static function subDays($day, $format = 'Y-m-d', $date = '')
    {
        if($date != ''){
            return Carbon::parse($date)->modify("-{$day} days")->toDateString();
        }
        return date($format, strtotime("-{$day} day"));
        //return Carbon::parse(date($format))->modify('-$day days')->toDateString();
    }

    /**
     * 取当前Y-m-d H:i:s前n天的Y-m-d H:i:s
     *
     * @param $day
     * @param string $date
     * @return string
     */
    public static function subDaysTime($day, $format = 'Y-m-d H:i:s')
    {
        return self::subDays($day, $format);
    }

    /**
     * 取当前Y-m-d H:i:s后n天的Y-m-d H:i:s
     *
     * @param $day
     * @param string $date
     * @return string
     */
    public static function addDays($day, $format = 'Y-m-d', $date = '')
    {
        if ($date) {
            return date($format, strtotime("+{$day} day", strtotime($date)));
        }
        return date($format, strtotime("+{$day} day"));
    }

    /**
     * 根据指定日期格式返回指定的日期格式
     * @param null $today_format
     * @return false|string
     */
    public static function getDateBy($date, $format = '-1day')
    {
        return date('Y-m-d', strtotime($format, strtotime($date)));
    }

    /**
     * 取当前Y-m-d H:i:s后n小时的Y-m-d H:i:s
     *
     * @param $hours
     * @param string $dateTime
     * @return Carbon
     */
    public static function addHours($hours, $dateTime = '')
    {
        if ($dateTime == '') {
            return Carbon::now()->addHours($hours);
        }
        return Carbon::parse($dateTime)->addHours($hours);
    }

    /**
     * 取当前Y-m-d H:i:s前n小时的Y-m-d H:i:s
     *
     * @param $hours
     * @param string $dateTime
     * @return Carbon
     */
    public static function subHours($hours, $dateTime = '')
    {
        if ($dateTime == '') {
            return Carbon::now()->subHours($hours);
        }
        return Carbon::parse($dateTime)->subHours($hours);
    }

    /**
     * @param $date
     * @return string
     */
    public static function startOfDay($date)
    {
        return date('Y-m-d', strtotime($date)) . ' 00:00:00';
    }

    /**
     * @param $date
     * @return string
     */
    public static function endOfDay($date)
    {
        return date('Y-m-d', strtotime($date)) . ' 23:59:59';
    }

    public static function format($dateTime, $format = 'Y-m-d H:i:s')
    {
        return date($format, strtotime($dateTime));
    }

    /**
     * 日期格式检验 Y-m-d Y/m/d
     */
    public static function checkDateIsValid($date, $formats = ['Y-m-d', 'Y/m/d', 'm/d/Y', 'd/m/Y', 'Y-n-d', 'Y/n/d', 'n/d/Y', 'd/n/Y'])
    {
        // php 不识别 d/m/Y  d/n/Y
        $date = str_replace('/', '-', $date);
        array_walk($formats, function (&$item) {
            $item = str_replace('/', '-', $item);
        });
        $unixTime = strtotime($date);
        // strtotime转换不对，日期格式显然不对。
        if (!$unixTime) {
            return false;
        }
        // 校验日期的有效性，只要满足其中一个格式就OK
        foreach ($formats as $format) {
            if (date($format, $unixTime) == $date) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $timestamp
     * @return false|string
     */
    public static function dateFormatByEnv($timestamp)
    {
        if (!$timestamp) {
            return '';
        }

        if (env('APP_TIMEZONE') == 'Asia/Shanghai') {
            return date('Y-m-d H:i:s', $timestamp);
        }

        return date('d-m-Y H:i:s', $timestamp);
    }

    /**
     * @param $startTime
     * @param null $endTime
     * @return string
     */
    public static function useHours($startTime, $endTime = null)
    {
        if (is_null($endTime)) {
            $endTime = time();
        }
        $times = $endTime - $startTime;
        $hoursStr = $minutesStr = $secondsStr = '';
        $hours = $minutes = $seconds = 0;
        if ($times > 0) {
            $hours = floor($times / 3600);
            $minutes = floor(($times - 3600 * $hours) / 60);
            $seconds = floor((($times - 3600 * $hours) - 60 * $minutes));
        }

        if ($hours) {
            $hoursStr = $hours . t('小时', 'approve');
        }

        if ($minutes) {
            $minutesStr = $minutes . t('分钟', 'approve');
        }

        if ($seconds) {
            $secondsStr = $seconds . t('秒', 'approve');
        }

        return $hoursStr . $minutesStr . $secondsStr;
    }

    /**
     * @param $time
     * @param string $format
     * @return false|string
     */
    public static function timestampToString($time, $format = 'd/m/Y')
    {
        if (!$time) {
            return '';
        }

        return date($format, (int)$time);
    }

    public static function getAge($birthday)
    {
        $dob = str_replace('/', '-', $birthday);
        if (strtotime($dob) !== false) {
            $y1 = date("Y", strtotime($dob));
            $y2 = date("Y", time());

            $age = $y2 - $y1;
        }
        return $age;
    }

}
