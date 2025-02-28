<?php

use Admin\Controllers\Staff\StaffController;
use Common\Services\Rbac\Controllers\RoleController;
use Laravel\Lumen\Routing\Router;
use Admin\Controllers\Staff\StaffStatisticsController;

/**
 * 人员列表
 *
 * @var Router $router
 */

$router->group([
    'path' => 'auth.staff',
], function (Router $router) {
    // 列表
    $router->get('staff/staff-index', StaffController::class . '@staffIndex');
    // 详情
    $router->get('staff/staff-view', StaffController::class . '@staffView');
    // 用户名验证
    $router->post('staff/verify-repeat', StaffController::class . '@verifyRepeat');
    // 创建
    $router->post('staff/create', StaffController::class . '@create');
    // 删除
    $router->post('staff/delete', StaffController::class . '@delete');
    // 禁用
    $router->post('staff/disable-or-enable', StaffController::class . '@disableOrEnable');
    // 密码设置
    $router->post('staff/password-setting', StaffController::class . '@passwordSetting');
    // 密码设置
    $router->post('staff/assign-role', StaffController::class . '@assignRole');
    //获取所有角色
    $router->get('rbac/role/all-role', RoleController::class . '@getAllrole');
    //获取特定角色
    $router->get('rbac/role/special-role', RoleController::class . '@getSpecialrole');
    //获取催收角色
    $router->get('staff/collection-level', StaffController::class . '@getCollectionLevel');
    //报表
    $router->get('staff/statistics', StaffStatisticsController::class . '@index');
});
