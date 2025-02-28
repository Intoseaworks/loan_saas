<?php

return array_merge(include "env.business.php", [
    'APP_ENV' => 'dev',
    'APP_DEBUG' => 'true',
    'APP_KEY' => 'UQ2pcGns6BWrLMBd8%%vbv5f6gBzzh6dd13V',
    'APP_TIMEZONE' => 'Asia/Manila',
    'APP_SQL_LOG' => 'false',
    // # 默认语言
    'APP_LOCALE' => 'en-US', //zh-CN
    'APP_FALLBACK_LOCALE' => 'en-US',
    // # 允许的语言
    'APP_LOCALE_ALLOW' => 'zh-CN,en-US',
    // # 语言额外参数
    'APP_LOCALE_PARAM' => 'locale',
    // # 接口错误日志
    'APP_ERROR_INFO' => 'true',
    // # 默认数据库
    'DB_HOST' => 'rm-wz9d49p46zm7puh27oo.mysql.rds.aliyuncs.com',
    'DB_USERNAME' => 'ucashrds',
    'DB_PASSWORD' => '123456@abc',
    'DB_PORT' => '3306',
    'DB_DATABASE' => 'scoreone_saas_peso',

    'CACHE_DRIVER' => 'redis',
    'QUEUE_DRIVER' => 'redis',
    // 风控数据库
    'DB_HOST_RISK' => 'rm-wz9d49p46zm7puh27oo.mysql.rds.aliyuncs.com',
    'DB_PORT_RISK' => '3306',
    'DB_DATABASE_RISK' => 'scoreone_saas_risk_peso',
    'DB_USERNAME_RISK' => 'ucashrds',
    'DB_PASSWORD_RISK' => '123456@abc',
    // #mail
    /* 'MAIL_DRIVER' => 'smtp',
      'MAIL_HOST' => 'smtpdm.aliyun.com',
      'MAIL_PORT' => '465',
      'MAIL_USERNAME' => 'postmaster@mailer.indiaox.in',
      'MAIL_ENCRYPTION' => 'ssl',
      'MAIL_PASSWORD' => '', */
    // 开发者
    'DEVELOPS' => 'nio.wang@scoreonetech.com',
    // #redis
    'REDIS_HOST' => '120.79.72.2',
    'REDIS_PORT' => '6379',
    'REDIS_DATABASE' => '1',
    'REDIS_PASSWORD' => 'ucashdev',
    // H5终端url
    'H5_CLIENT_DOMAIN' => "https://h5.dev.indiaox.in",
    // API域名地址
    'API_CLIENT_DOMAIN' => "http://120.78.230.66:8081",
    // 是否开启短信发送
    'HAS_SMS_ON' => 'false',
    // 自动代付开关
    'AUTO_REMIT' => 'false',
    // 印牛服务配置 生产配置
    'SERVICES_APP_KEY' => '8dw5Yvrv212FljhdM1t',
    'SERVICES_APP_SECRET_KEY' => 'f1d6b5744f72762719b1f897268a2151',
    'SERVICES_ENDPOINT' => "http://services.dev.indiaox.in/",
    'SERVICES_INNER_ENDPOINT' => "http://services.dev.indiaox.in/",
    'PROJECT_NAME' => 'Urupee OxSaas',
    'RISK_ENDPOINT' => 'http://120.78.230.66:8080/',
    // rbac 关闭 1开启 0关闭
    'RBAC_CLOSED' => '1',
    // rbac 超级管理员
    'RBAC_SUPER' => "[]",
    // Pusher
    'PUSHER_APP_ID' => '967856',
    'PUSHER_KEY' => '62fa06f1ab8b6b6415c2',
    'PUSHER_SECRET' => '056a90d8cbe85964c2fd',
    'JWT_SECRET' => 'e3AlvBJQdr9n1g8gft8q4LHoECLVsCwp',
    // 机审总开关
    'SYSTEM_APPROVE' => 'true',
    // 机审任务执行开关
    'SYSTEM_APPROVE_EXEC' => 'true',
    // oss 配置
    

    
    // bucket => 访问权限  true 公共仓库,false私有仓库.默认使用第一个bucket.如果使用其他的需要声明:Storage::disk('oss')->setBucket($bucket);
    // env()不能设置数组,这里使用json
    'OSS_BUCKETS' => '{"urupee-img":false}',
    'OSS_ENDPOINT' => 'oss-ap-south-1.aliyuncs.com',
    'OSS_ENDPOINT_INTERNAL' => 'oss-ap-south-1-internal.aliyuncs.com',
    //razorpay
    "PAY_RAZORPAY_KEY_ID" => "rzp_test_HT7c4fJCd9leKO",
    "PAY_RAZORPAY_KEY_SECRET" => "9wjizHgAqBjEXt2VAeu8yN1x",
    "PAY_RAZORPAY_MY_SECRET" => "urupeeal23ljflj23kh23lk23hjk234",
    "PAY_RAZORPAY_MY_ACC" => "2323230087044190",

    'DING_TOKEN' => 'f802ec64d2baccfe8883d30e3bf3144e11dec55eca2f414cf41b5a2d3783e4f6',
]);
