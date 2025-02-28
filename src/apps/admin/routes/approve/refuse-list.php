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
 * 审批拒绝订单
 *
 * @var Router $router
 */
$router->group([
    'namespace' => 'Admin\Controllers\NewApprove',
    'path' => 'approval-management.refuselist',
], function (Router $router) {
    // 人工审批被拒列表
    $router->get('approve/reject-list', 'NewApproveController@rejectList');
    // 人工审批被拒原因
    $router->get('approve/reject-reason', 'NewApproveController@rejectReason');
});
