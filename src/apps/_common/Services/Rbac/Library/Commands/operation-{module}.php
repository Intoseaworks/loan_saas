<?php
/**
 * [
 * //id 不要修改,因为涉及到修改和删除
 * 'id' => '',
 * //功能名称
 * 'name' => '增加',
 * //权限Id
 * 'permissions' => [],
 * //菜单
 * 'menu_key' => '',
 * //模块
 * 'guard_name' => 'admin',
 * ],
 */
return [
    [
        'id' => 1,
        'name' => '增加',
        'permissions' => [
            'GET/api/admin/community',
            'POST/api/admin/community',
            'GET/api/admin/community/{id:[0-9]*}',
        ],
        'menu_key' => 'admin.address/index',
    ],
];