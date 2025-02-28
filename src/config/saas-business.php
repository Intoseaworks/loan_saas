<?php

return [
    //用户认证项配置
    'user_auth_type_is_completed' => json_decode(
        env('USER_AUTH_TYPE_IS_COMPLETED', json_encode(\Common\Models\User\UserAuth::TYPE_IS_COMPLETED)),
        true),
    'user_optional_auth_type_is_completed' => json_decode(
        env('USER_OPTIONAL_AUTH_TYPE_IS_COMPLETED'),
        true),
    //app列表认证项
    'user_app_auth' => json_decode(
        env('USER_APP_AUTH', json_encode(array_keys(\Common\Models\User\UserAuth::AUTH))),
        true),
    //app列表认证选填项
    'user_app_optional_auth' => json_decode(
        env('USER_APP_OPTIONAL_AUTH', json_encode(array_keys(\Common\Models\User\UserAuth::AUTH))),
        true),
    //审批tabs
    'approve_tabs' => json_decode(
        env('APPROVE_TABS', json_encode([])),
        true),
    //用户信息tabs
    'user_info_tabs' => json_decode(
        env('USER_INFO_TABS', json_encode([])),
        true),
    //可以选择的运营商
    'user_auth_providers' => json_decode(
        env('USER_AUTH_PROVIDERS', json_encode([])),
        true),
];
