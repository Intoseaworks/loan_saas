<?php

return [
    // 机审总开关
    'system_approve' => env('RISK_SYSTEM_APPROVE', true),
    // 机审征信总开关
    'system_approve_credit' => env('RISK_SYSTEM_APPROVE_CREDIT', false),
];
