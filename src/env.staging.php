<?php

return array_merge(include "env.business.php", [
    'APP_ENV' => 'prod',
    'APP_DEBUG' => 'true',
    'APP_KEY' => 'UQ2pcGns6BWrLMBd8%%vbv5f6gBzzh6dd13V',
    'APP_TIMEZONE' => 'Asia/Manila',
    'APP_SQL_LOG' => 'false',

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
    'DB_HOST' => 'rm-a2d1333s9gp40460i.mysql.ap-south-1.rds.aliyuncs.com',
    'DB_USERNAME' => 'root',
    'DB_PASSWORD' => 'HGd45#e@K',
    'DB_PORT' => '3306',
    'DB_DATABASE' => 'loan_saas_db',

    // 风控数据库
    'DB_HOST_RISK' => 'rm-a2d1333s9gp40460i.mysql.ap-south-1.rds.aliyuncs.com',
    'DB_PORT_RISK' => '3306',
    'DB_DATABASE_RISK' => 'urupee_risk_db',
    'DB_USERNAME_RISK' => 'root',
    'DB_PASSWORD_RISK' => 'HGd45#e@K',


    'CACHE_DRIVER' => 'redis',
    'QUEUE_DRIVER' => 'redis',

    // #mail
    /*'MAIL_DRIVER' => 'smtp',
    'MAIL_HOST' => 'smtpdm.aliyun.com',
    'MAIL_PORT' => '465',
    'MAIL_USERNAME' => 'postmaster@mailer.urupee.in',
    'MAIL_ENCRYPTION' => 'ssl',
    'MAIL_PASSWORD' => '',*/
    // 开发者
    'DEVELOPS' => 'xxx@gmail.com;xxa@gmail.com',

    // #redis
    'REDIS_HOST' => 'r-a2dyz82ruar58givzv.redis.ap-south-1.rds.aliyuncs.com',
    'REDIS_PORT' => '6379',
    'REDIS_DATABASE' => '1',
    'REDIS_PASSWORD' => 'Yt1RF%kS',

    // H5终端url
    'H5_CLIENT_DOMAIN' => "https://apph5.urupee.in",

    // API域名地址
    'API_CLIENT_DOMAIN' => "https://saas.e-perash.com",

    // 是否开启短信发送
    'HAS_SMS_ON' => 'true',

    // 是否开启App推送
    'HAS_APP_PUSH_ON' => 'true',

    // 自动代付开关
    'AUTO_REMIT' => 'true',

    // 印牛服务配置 生产配置
    'SERVICES_APP_KEY' => 'xrzLC4931smVZ',
    'SERVICES_APP_SECRET_KEY' => '1ba784b8f1a40d4381eaeb37350971e1',
    'SERVICES_ENDPOINT' => "https://services.indiaox.in/",
    'SERVICES_INNER_ENDPOINT' => "https://services.indiaox.in/",

    // 风控地址，配成自己的地址的话一般是与 API_CLIENT_DOMAIN 一致
    // 远端风控：https://services.indiaox.in/ 本地风控：https://saas.urupee.in/
    'RISK_ENDPOINT' => 'https://services.indiaox.in/',

    'PROJECT_NAME' => 'Urupee OxSaas',

    // rbac 关闭 1开启 0关闭
    'RBAC_CLOSED' => '1',
    // rbac 超级管理员
    'RBAC_SUPER' => "[]",

    // Pusher
    'PUSHER_APP_ID' => '967856',
    'PUSHER_KEY' => '62fa06f1ab8b6b6415c2',
    'PUSHER_SECRET' => '056a90d8cbe85964c2fd',

    'JWT_SECRET' => 'e3AlvBJQdr9n1g8gft8q4LHoECLVsCwp',

    // 机审总开关-业务
    'SYSTEM_APPROVE' => 'true',
    // 机审任务执行开关-业务
    'SYSTEM_APPROVE_EXEC' => 'true',

    // 机审总开关
    'RISK_SYSTEM_APPROVE' => 'true',
    // 机审征信总开关
    'RISK_SYSTEM_APPROVE_CREDIT' => 'false',

    // oss 配置
    
    
    // bucket => 访问权限  true 公共仓库,false私有仓库.默认使用第一个bucket.如果使用其他的需要声明:Storage::disk('oss')->setBucket($bucket);
    // env()不能设置数组,这里使用json
    'OSS_BUCKETS' => '{"urupee-img":false}',
    'OSS_ENDPOINT' => 'oss-ap-south-1.aliyuncs.com',
    'OSS_ENDPOINT_INTERNAL' => 'oss-ap-south-1-internal.aliyuncs.com',
]);
