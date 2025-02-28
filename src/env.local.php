<?php

return array_merge(include "env.business.php", [
    'APP_ENV' => 'dev',
    'APP_DEBUG' => 'true',
    'APP_KEY' => 'UQ2pcGns6BWrLMBd8%%vbv5f6gBzzh6dd13V',
    'APP_TIMEZONE' => 'Asia/Manila',
    'APP_SQL_LOG' => 'true',

    // # 默认语言
    'APP_LOCALE' => 'en-US', //zh-CN
    'APP_FALLBACK_LOCALE' => 'zh-CN',
    // # 允许的语言
    'APP_LOCALE_ALLOW' => 'zh-CN,en-US',
    // # 语言额外参数
    'APP_LOCALE_PARAM' => 'locale',
    // # 接口错误日志
    'APP_ERROR_INFO' => 'true',

    // # 默认数据库
    'DB_HOST' => '127.0.0.1',
    'DB_USERNAME' => 'root',
    'DB_PASSWORD' => '',
    'DB_PORT' => '3307',
    'DB_DATABASE' => 'loan_saas_db',

    // 风控数据库
    'DB_HOST_RISK' => '127.0.0.1',
    'DB_PORT_RISK' => '3306',
    'DB_DATABASE_RISK' => 'urupee_risk_db',
    'DB_USERNAME_RISK' => 'root',
    'DB_PASSWORD_RISK' => '',


    'CACHE_DRIVER' => 'redis',
    'QUEUE_DRIVER' => 'redis',

    // #mail
    /*'MAIL_DRIVER' => 'smtp',
    'MAIL_HOST' => 'smtpdm.aliyun.com',
    'MAIL_PORT' => '465',
    'MAIL_USERNAME' => 'postmaster@mailer.indiaox.in',
    'MAIL_ENCRYPTION' => 'ssl',
    'MAIL_PASSWORD' => '',*/
    // 开发者
    'DEVELOPS' => 'xxx@gmail.com;xxa@gmail.com',

    // #redis
    'REDIS_HOST' => '127.0.0.1',
    'REDIS_PORT' => '6380',
    'REDIS_DATABASE' => '1',
    'REDIS_PASSWORD' => '',

    // H5终端url
    'H5_CLIENT_DOMAIN' => "https://h5.dev.indiaox.in",

    // API域名地址
    'API_CLIENT_DOMAIN' => "https://saas.dev.indiaox.in",

    // 是否开启短信发送
    'HAS_SMS_ON' => 'false',

    // 是否开启App推送
    'HAS_APP_PUSH_ON' => 'true',

    // 自动代付开关
    'AUTO_REMIT' => 'false',

    // 印牛服务配置
    'SERVICES_APP_KEY' => '8dw5Yvrv212FljhdM1t',
    'SERVICES_APP_SECRET_KEY' => 'f1d6b5744f72762719b1f897268a2151',
    'SERVICES_ENDPOINT' => "http://services.dev.indiaox.in/",
    'SERVICES_INNER_ENDPOINT' => "http://services.dev.indiaox.in/",

    // 风控地址，配成自己的地址的话一般是与 API_CLIENT_DOMAIN 一致
    'RISK_ENDPOINT' => 'https://saas.dev.indiaox.in/',

    'PROJECT_NAME' => 'Urupee OxSaas',

    // rbac 关闭 1开启 0关闭
    'RBAC_CLOSED' => '1',
    // rbac 超级管理员
    'RBAC_SUPER' => "[]",

    'JWT_SECRET' => '7U4i5xQWKQ4jQ1I6x9vmU1DOyqcyJO2t',

    // oss 配置
    

    
    // bucket => 访问权限  true 公共仓库,false私有仓库.默认使用第一个bucket.如果使用其他的需要声明:Storage::disk('oss')->setBucket($bucket);
    // env()不能设置数组,这里使用json
    'OSS_BUCKETS' => '{"jqb-loan":false}',
    'OSS_ENDPOINT' => 'oss-cn-shenzhen.aliyuncs.com',
    'OSS_ENDPOINT_INTERNAL' => 'oss-cn-shenzhen-internal.aliyuncs.com',
    //razorpay
    "PAY_RAZORPAY_KEY_ID" => "rzp_test_HT7c4fJCd9leKO",
    "PAY_RAZORPAY_KEY_SECRET" => "9wjizHgAqBjEXt2VAeu8yN1x",
    "PAY_RAZORPAY_MY_SECRET" => "urupeeal23ljflj23kh23lk23hjk234",
    "PAY_RAZORPAY_MY_ACC" => "2323230087044190",
]);
