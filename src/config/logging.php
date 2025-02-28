<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            //按天生成log文件
            'channels' => ['daily'],
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/lumen.log'),
//            /** 自定义日志目录 */
//            'path' => '/data/logs/project/loan-saas/lumen.log',
            'level' => 'debug',
            //保留15天的日志
            'days' => 15,
            'permission'=>0777
        ],

        // 审批系统
        'approve' => [
            //按日期生成日志文件
            'driver' => 'daily',
            'path' => storage_path('logs/approve.log'),
            //存储位置,注意文件夹读写权限.文件不存在会自动创建
//            'path' => '/data/logs/project/loan-saas/approve/' . date('Y-m') . '/approve.log',
            'level' => 'debug',
            'days' => 15,
            'permission'=>0777
        ],
    ],

];
