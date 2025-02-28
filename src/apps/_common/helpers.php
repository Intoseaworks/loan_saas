<?php

if (!function_exists('t')) {
    /**
     * t 中英文
     * @param $key
     * @param $group
     * @param array $replace
     * @param null $locale
     * @return string
     */
    function t($key, $group = 'messages', $replace = [], $locale = null)
    {
        if($locale == null){
            $locale = app('translator')->getLocale();
        }
        $id = $group . '.' . $key;
        $trans = trans($group . '.' . $key, $replace, $locale);
        if ($trans == $id) {
            return $key;
        }

        return $trans;
    }

    function ts($arr, $group = 'messages')
    {
        foreach ($arr as $key => $val) {
            $arr[$key] = t($val, $group);
        }
        return $arr;
    }

    if (!function_exists('mysqlTimeZone')) {
        /**
         * 根据环境计算MySQL时区
         * @return string
         */
        function mysqlTimeZone()
        {
            return '+08:00';
        }
    }

    if (!function_exists('getDatabaseName')) {
        /**
         * 获取数据库名称
         *
         * @return string
         */
        function getDatabaseName($connection)
        {
            // 默认数据库
            if($connection == null){
               $connection = config('database.default');
            }
            return config("database.connections.{$connection}.database");
        }
    }

}
