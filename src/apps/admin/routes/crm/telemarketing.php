<?php

use Admin\Controllers\Crm\TelemarketingController;
use Laravel\Lumen\Routing\Router;
use Admin\Controllers\Collection\CollectionCallController;
/**
 * 电销
 *
 * @var Router $router
 */
$router->group([
    'path' => 'crm-telemarketing',
        ], function (Router $router) {
    //待电销
    $router->post('crm/telemarketing/sales-list', TelemarketingController::class . '@salesList');
    //电销完成
    $router->post('crm/telemarketing/finish', TelemarketingController::class . '@finish');
    //电销记录
    $router->post('crm/telemarketing/telemarketing-record', TelemarketingController::class . '@telemarketingRecord');
    //电销分配
    $router->post('crm/telemarketing/assign', TelemarketingController::class . '@assign');
    //电销取消分配
    $router->post('crm/telemarketing/cancel-assign', TelemarketingController::class . '@cancelAssign');
    //电销日报
    $router->post('crm/telemarketing/report', TelemarketingController::class . '@report');
    //添加电销记录
    $router->post('crm/telemarketing/add-record', TelemarketingController::class . '@addRecord');
    //添加电销记录
    $router->post('crm/telemarketing/task-list', TelemarketingController::class . '@taskList');
    //添加电销记录
    $router->post('crm/telemarketing/task-customer-count', TelemarketingController::class . '@getCustomerCount');
    $router->post('collection/call-setting/call', CollectionCallController::class . '@call');
});
