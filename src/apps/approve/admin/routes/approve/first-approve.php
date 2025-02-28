<?php

use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([
    'namespace' => 'Approve\Admin\Controllers',
    'path' => 'approval-management.Approval.Approval-list',
], function (Router $router) {
    // 审批列表
    $router->get('first-approve/list', 'ApproveController@index');
    // 开始审批
    $router->get('first-approve/start-work', 'ApproveController@startWork');
    // 停止审批
    $router->get('first-approve/stop-work', 'ApproveController@stopWork');
    // 初审详情页
    $router->get('first-approve/detail', 'ApproveController@firstShow');
    // 初审提交
    $router->post('first-approve/submit', 'ApproveController@firstSubmit');
    // 地址信息
    $router->get('first-approve/address-info', 'ApproveController@addressInfo');
    # 案件退回
    $router->post('first-approve/back-case', 'ApproveController@backCase');
});
