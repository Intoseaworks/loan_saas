<?php
/**
 * 角色初始化
 * `menus`对应的是`rbac_menus`表的path
 * @see \Common\Console\Commands\Permissions\InitRoleConsole
 */

return [
    [
        'is_super' => true,
        'name' => '超级管理员',
        'guard_name' => 'admin',
        'menus' => [],
    ],
    [
        'name' => '催收管理员',
        'guard_name' => 'admin',
        'menus' => [
            // 贷后管理
            //'admin.post-management',
            // 催收管理->催收订单
            'admin.collection-management.collectionorder',
            // 催收统计
            'admin.statistics.urgeRegain-statistics',
            // 系统设置->催收设置
            'admin.system-settings.collection-settings',
            // 工作台
            'admin.work_bench',
        ],
    ],
    [
        'name' => '审批管理员',
        'guard_name' => 'admin',
        'menus' => [
            // 用户管理->用户信息
            'admin.user-management.userlist',
            // 用户管理->黑名单用户
            'admin.user-management.blacklist',
            // 借款管理
            'admin.borrowing',
            // 审批管理
            'admin.approval-management',
            // 系统设置->审批设置
            'admin.system-settings.approval-settings',
        ],
    ],
    [
        'name' => '财务管理员',
        'guard_name' => 'admin',
        'menus' => [
            // 用户管理->用户信息
            'admin.user-management.userlist',
            // 用户管理->黑名单用户
            'admin.user-management.blacklist',
            // 借款管理
            'admin.borrowing',
            // 支付管理
            'admin.payment-management',
            // 还款管理
            'admin.repayment-management',
            // 贷后管理
            //'admin.post-management',
            // 工作台
            'admin.work_bench',
            // 运营数据
            'admin.statistics.operational',
        ],
    ],
    [
        'name' => '运营管理员',
        'guard_name' => 'admin',
        'menus' => [
            // 用户管理
            'admin.user-management',
            // 借款管理
            'admin.borrowing',
            // 系统设置->运营设置
            'admin.system-settings.operate-settings',
            // 系统设置->贷款设置
            'admin.system-settings.loan-settings',
            // 通知管理
            'admin.inform-management',
            // 工作台
            'admin.work_bench',
        ],
    ],
];
