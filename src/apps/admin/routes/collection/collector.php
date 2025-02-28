<?php

use Admin\Controllers\Collection\CollectionCallController;
use Admin\Controllers\Collection\CollectionController;
use Laravel\Lumen\Routing\Router;
use Admin\Controllers\Staff\StaffController;
use Common\Services\Rbac\Controllers\RoleController;
use Admin\Controllers\Staff\StaffStatisticsController;

/**
 * 催收订单
 *
 * @var Router $router
 */
$router->group([
    'path' => 'collection-management.collectionorder',
        ], function (Router $router) {
    $router->post('collection/online-report', CollectionController::class . '@report');
    $router->post('collection/call-setting/list', CollectionCallController::class . '@index');
    $router->post('collection/call-setting/create', CollectionCallController::class . '@create');
    $router->post('collection/call-setting/status', CollectionCallController::class . '@status');
    $router->post('collection/call-setting/call', CollectionCallController::class . '@call');
    $router->post('collection/sendmail', CollectionController::class . '@sendmail');
    // 禁用
    $router->post('staff/disable-or-enable', StaffController::class . '@disableOrEnable');
    //获取特定角色
    $router->get('rbac/role/special-role', RoleController::class . '@getSpecialrole');
    //获取催收角色
    $router->get('staff/collection-level', StaffController::class . '@getCollectionLevel');
    // 用户名验证
    $router->post('staff/verify-repeat', StaffController::class . '@verifyRepeat');
    // 密码设置
    $router->post('staff/assign-role', StaffController::class . '@assignRole');
    // 创建
    $router->post('staff/create', StaffController::class . '@create');
    // 密码设置
    $router->post('staff/password-setting', StaffController::class . '@passwordSetting');
    // 删除
    $router->post('staff/delete', StaffController::class . '@delete');
    //报表
    $router->get('staff/statistics', StaffStatisticsController::class . '@index');
    //获取所有角色
    $router->get('rbac/role/all-role', RoleController::class . '@getAllrole');

    
    $router->get('collection/auto-call-setting/index', CollectionCallController::class . '@autoCallIndex');
    $router->post('collection/auto-call-setting/save', CollectionCallController::class . '@autoCallSave');
    $router->post('collection/auto-call-setting/remove', CollectionCallController::class . '@autoCallRemove');
    
});
