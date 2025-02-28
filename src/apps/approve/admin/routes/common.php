<?php

use Approve\Admin\Controllers\ApproveController;
use Approve\Admin\Controllers\ApproveCheckController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */

$router->group([
    'prefix' => 'api/approve',
], function (Router $router) {
    $router->get('approve/log', ApproveController::class . '@approveLog');
    $router->get('approve/start-check', ApproveController::class . '@startCheck');
    // 订单状态列表
    $router->get('approve-check/order-status-list', ApproveCheckController::class.'@orderStatusList');
    //权限403
    $router->post('call-approve/calllog-list', ApproveController::class .'@callLogList');
    $router->get('/options', ApproveController::class.'@options');
});

