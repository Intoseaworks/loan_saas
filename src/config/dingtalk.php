<?php

use Common\Utils\DingDing\DingHelper;

return [
    'robot_base_url' => env('DING_ROBOT_URL', 'https://oapi.dingtalk.com/robot/send'),
    'timeout' => 2.0,
    'access_token' => [
        'default' => env('DING_TOKEN', 'f802ec64d2baccfe8883d30e3bf3144e11dec55eca2f414cf41b5a2d3783e4f6'),// 默认
        'others' => [ // 扩展更多token
            DingHelper::ROBOT_APP_EXCEPTION => '193936f6474bab1cc0d65c465e4350ba3a8b2e0dd56a22a8b61a62c008787e57',
        ],
    ],
];
