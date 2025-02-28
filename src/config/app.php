<?php

# 根据前端cookie读语言包
$locale = $_COOKIE['umi_locale'] ?? env('APP_LOCALE', 'en-US');
return [
    // ide-helper 扫描使用.不需要加载
    'aliases' => [

    ],

    'locale' => $locale,
];
