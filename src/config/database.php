<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PDO Fetch Style
    |--------------------------------------------------------------------------
    |
    | By default, database results will be returned as instances of the PHP
    | stdClass object; however, you may desire to retrieve records in an
    | array format for simplicity. Here you can tweak the fetch style.
    |
    */

    'fetch' => PDO::FETCH_CLASS,

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'mysql' => [
            'write' => [
                'host' => env('DB_HOST', 'localhost'),
                'username' => env('DB_USERNAME', 'forge'),
                'password' => env('DB_PASSWORD', ''),
            ],
            'read' => [
                'host' => env('DB_HOST_READONLY', 'localhost'),
                'username' => env('DB_USERNAME_READONLY', 'forge'),
                'password' => env('DB_PASSWORD_READONLY', ''),
            ],
            'driver' => 'mysql',
//            'host' => env('DB_HOST', 'localhost'),
            'database' => env('DB_DATABASE', 'forge'),
//            'username' => env('DB_USERNAME', 'forge'),
//            'password' => env('DB_PASSWORD', ''),
            'port' => env('DB_PORT', 3306),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'timezone' => env('DB_TIMEZONE', mysqlTimeZone()),
            'strict' => env('DB_STRICT_MODE', false),
            'sticky' => true
        ],
        'mysql_readonly' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_READONLY', 'localhost'),
            'port' => env('DB_PORT_READONLY', 3306),
            'database' => env('DB_DATABASE_READONLY', 'forge'),
            'username' => env('DB_USERNAME_READONLY', 'forge'),
            'password' => env('DB_PASSWORD_READONLY', ''),
            'charset' => env('DB_CHARSET_READONLY', 'utf8mb4'),
            'collation' => env('DB_COLLATION_READONLY', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX_READONLY', ''),
            'timezone' => env('DB_TIMEZONE_READONLY', mysqlTimeZone()),
            'strict' => env('DB_STRICT_MODE_READONLY', false),
        ],
        // 中台数据
        'mysql_focus' => [
            'host' => env('DB_HOST_FOCUS', 'localhost'),
            'port' => env('DB_PORT_FOCUS', 3306),
            'database' => env('DB_DATABASE_FOCUS', 'forge'),
            'username' => env('DB_USERNAME_FOCUS', 'forge'),
            'password' => env('DB_PASSWORD_FOCUS', ''),
            'driver' => 'mysql',
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'timezone' => env('DB_TIMEZONE', mysqlTimeZone()),
            'strict' => env('DB_STRICT_MODE', false),
        ],
        // 风控库
        'mysql_risk' => [
            'host' => env('DB_HOST_RISK', 'localhost'),
            'port' => env('DB_PORT_RISK', 3306),
            'database' => env('DB_DATABASE_RISK', 'forge'),
            'username' => env('DB_USERNAME_RISK', 'forge'),
            'password' => env('DB_PASSWORD_RISK', ''),
            'driver' => 'mysql',
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'timezone' => env('DB_TIMEZONE', mysqlTimeZone()),
            'strict' => env('DB_STRICT_MODE', false),
        ],
        // clm_prod_ph
        'clm_prod_ph' => [
            'host' => '47.74.246.6',
            'port' => 3336,
            'database' => 'clm_prod_ph',
            'username' => 'roc_yu',
            'password' => 'Yupeng78',
            'driver' => 'mysql',
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'timezone' => env('DB_TIMEZONE', mysqlTimeZone()),
            'strict' => env('DB_STRICT_MODE', false),
        ],
        // dcn_ccsdb
        'dcn_ccsdb' => [
            'host' => '47.74.246.6',
            'port' => 3336,
            'database' => 'dcn_ccsdb',
            'username' => 'roc_yu',
            'password' => 'Yupeng78',
            'driver' => 'mysql',
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'timezone' => env('DB_TIMEZONE', mysqlTimeZone()),
            'strict' => env('DB_STRICT_MODE', false),
        ],
        // s2_prod
        's2_prod' => [
            'host' => '47.74.246.6',
            'port' => 3336,
            'database' => 's2_prod',
            'username' => 'roc_yu',
            'password' => 'Yupeng78',
            'driver' => 'mysql',
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'timezone' => env('DB_TIMEZONE', mysqlTimeZone()),
            'strict' => env('DB_STRICT_MODE', false),
        ],
        // u3_prod_u1
        'u3_prod_u1' => [
            'host' => 'rm-t4n14qj90v32k9dd06o.mysql.singapore.rds.aliyuncs.com',
            'port' => 3306,
            'database' => 'u3_prod_u1',
            'username' => 'roc_yu',
            'password' => 'Yupeng78',
            'driver' => 'mysql',
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'timezone' => env('DB_TIMEZONE', mysqlTimeZone()),
            'strict' => env('DB_STRICT_MODE', false),
        ],
        # 备库
        'back_up_db' => [
            'driver' => 'mysql',
            'host' => "rm-t4n14qj90v32k9dd0.mysql.singapore.rds.aliyuncs.com",
            'port' => 3306,
            'database' => 'saas_eperash_db',
            'username' => 'nio',
            'password' => 'nio@123456',
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'timezone' => env('DB_TIMEZONE', mysqlTimeZone()),
            'strict' => env('DB_STRICT_MODE', false),
        ]

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [
        'options' => [
            'prefix' => '{SAAS}',
        ],
        'cluster' => env('REDIS_CLUSTER', false),

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DATABASE', 0),
            'password' => env('REDIS_PASSWORD', null),
            'prefix' => env('REDIS_PREFIX', '{SAAS}')
        ],

        'services' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_SERVICES_DATABASE', 8),
            'password' => env('REDIS_PASSWORD', null),
            'prefix' => env('REDIS_PREFIX', '{SAAS}')
        ],
    ],

];
