<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

use Laravel\Lumen\Routing\Router;

/**
 * 人工审批
 *
 * @var Router $router
 */
$router->group([
    'namespace' => 'Admin\Controllers\Approve',
    'path' => 'approval-management.artificiallist',
], function (Router $router) {
    // 人工审批列表(分配审批单)
    $router->get('approve/approve-list', 'ApproveController@approveList');
    // 获取人工审批选项列表
    $router->get('approve/select-group', 'ApproveController@approveSelectGroup');
    // 操作审批|提交审批
    $router->post('approve/approve-submit', 'ApproveController@approveSubmit');
    // 人工审批详情
    $router->get('approve/view', 'ApproveController@view');
    // 判断订单能否进入审批
    $router->get('approve/can-approve', 'ApproveController@canApprove');
});
