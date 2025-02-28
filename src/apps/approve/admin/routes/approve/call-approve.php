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
    $router->get('call-approve/list', 'ApproveController@index');
    // 开始审批
    $router->get('call-approve/start-work', 'ApproveController@startWork');
    // 停止审批
    $router->get('call-approve/stop-work', 'ApproveController@stopWork');
    // 电审详情页
    $router->get('call-approve/detail', 'ApproveController@callShow');
    // 电审提交
    $router->post('call-approve/submit', 'ApproveController@callSubmit');
    // 电审保存
    $router->post('call-approve/submit-draft', 'ApproveController@callSubmitDraft');

    $router->post('call-approve/add-calllog', 'ApproveController@addCallLog');
//    $router->post('call-approve/calllog-list', 'ApproveController@callLogList');
    $router->post('call-approve/calllog-select', 'ApproveController@callLogSelect');
    // 电审保存
    $router->post('call-approve/only-save', 'ApproveController@onlySave');
});
