<?php

return [
    'config' => [
        "master" => true,
        "name" => "后端",
        // rbac表所在的数据库
        "connection" => "mysql",
        // 超级管理员,用户Id
        "super" => json_decode(env('RBAC_SUPER', '[]'), true),
        // 是否关闭rbac
        "closed" => env('RBAC_CLOSED', '1') == '0' ? true : false,
    ],
];
