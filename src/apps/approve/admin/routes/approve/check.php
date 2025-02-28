<?php

use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([
    'namespace' => 'Approve\Admin\Controllers',
    'path' => 'approval-management.ApprovalManagement.ApprovalManagement-list',
], function (Router $router) {
    // 审批查看列表
    $router->get('approve-check/list', 'ApproveCheckController@index');
    // 优先权设置
    $router->get('approve-check/set-priority', 'ApproveCheckController@setPriority');
    // 订单状态列表,调到普通路由列表
//    $router->get('approve-check/order-status-list', 'ApproveCheckController@orderStatusList');
    // 审批状态列表
    $router->get('approve-check/approve-status-list', 'ApproveCheckController@approveStatusList');
    // 审批查看详情
    $router->get('approve-check/detail', 'ApproveCheckController@show');

    # 案件退回
    $router->post('approve-check/back-case', 'ApproveCheckController@backCase');
    # 案件转案
    $router->post('approve-check/turn-case', 'ApproveCheckController@turnCase');
});
