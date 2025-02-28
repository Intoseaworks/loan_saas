<?php

return [
    'public_services_config' => [  //印牛服务配置
        'app_key' => env('SERVICES_APP_KEY', ''),
        'app_secret_key' => env('SERVICES_APP_SECRET_KEY', ''),
        'endpoint' => env('SERVICES_INNER_ENDPOINT', ''),
        'outer_endpoint' => env('SERVICES_ENDPOINT', ''),
        'risk_endpoint' => env('RISK_ENDPOINT', ''), // 风控的APP_KEY需要根据 setMerchantId 动态变化
    ],

    'project_name' => env('PROJECT_NAME'),

    //crm配置（回访，审批，催收）
    'public_crm_config' => [
        'app_key' => env('CRM_APP_KEY', 'jcqe4tyjdddqY8yur67sj1NY1Hto'),
        'app_secret_key' => env('CRM_SECRET_KEY', 'a8b50e833swpt8c4ffi9c0f421sdafsd9345341c70d0bd7c4350'),
        'endpoint' => env('CRM_ENDPOINT', 'https://approve.cashfintech.net/'),
    ],
];
