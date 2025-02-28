<?php

return [
    /**
     * debug
     */
    'debug' => env('APP_DEBUG', true),
    /**
     * app_env
     */
    'app_env' => env('APP_ENV', 'local'),
    /**
     * app SQL log
     */
    'app_sql_log' => env('APP_SQL_LOG', false),
    /**
     * app upload oss
     */
    'app_upload_oss' => env('APP_UPLOAD_OSS', true),
    /**
     * app upload oss 是否阿里内网进行上传
     */
    'app_upload_oss_internal' => env('APP_UPLOAD_OSS_INTERNAL', false),
    /**
     * 是否开启短信发送
     */
    'has_sms_on' => env('HAS_SMS_ON', false),
    /**
     * 是否开启App推送
     */
    'has_app_push_on' => env('HAS_APP_PUSH_ON', false),
    /**
     * H5终端url
     */
    'h5_client_domain' => env('H5_CLIENT_DOMAIN'),
    /**
     * 邀请码是否开放
     */
    'invite_user' => true,
    /**
     * 邀请返现是否开放
     */
    'invite_friends_cashback' => ['isOn'=>true,'date'=>'2020-12-30'],
    /**
     * API终端url
     */
    'api_client_domain' => env('API_CLIENT_DOMAIN'),
    /**
     * 代付代扣渠道
     */
    'auto_pay_channel' => explode(',', env('AUTO_PAY_CHANNEL', '')),

    #钉钉账号
    'ding_appid' => env('DING_APPID'),
    'ding_appsecret' => env('DING_APPSECRET'),
    #钉钉回调地址
    'ding_login_callback' => env('DING_LOGIN_CALLBANK'),

    #开发者
    'develops' => explode(';', env('DEVELOPS')),

    /** Api Token 过期时间 */
    'access_token_ttl' => 3600 * 2,
    'refresh_token_ttl' => 3600 * 24 * 7,
    /** 短信验证码过期时间 */
    'sms_captcha_ttl' => 60 * 5,

    // faceId
    'faceid_app_key' => 'm_mDINsn0P3o507MBB-iT1IfgdQh9OuY',
    'faceid_app_secret' => 'kKQT4aJPewgGukovVAhSPzWpAg8vtpFJ',

    // 极光配置
    'jiguang_app_key' => env('JIGUANG_APP_KEY'),
    'jiguang_sectet_key' => env('JIGUANG_SECTET_KEY'),

    // 自动放款总开关
    'auto_remit' => env('AUTO_REMIT', false),

    // 机审总开关-业务
    'system_approve' => env('SYSTEM_APPROVE', true),
    // 机审任务执行开关-业务
    'system_approve_exec' => env('SYSTEM_APPROVE_EXEC', true),
    "excluded_from_black" => [
        \Common\Models\Risk\RiskBlacklist::KEYWORD_EMAIL => [
            "ABC123@GMAIL.COM",
            ],
        \Common\Models\Risk\RiskBlacklist::KEYWORD_TELEPHONE => [
            "9876543212",
            "9876543211",
            "9876543213",
        ],
    ]
];
