<?php

use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([
    'middleware' => ['excel'],
    'namespace' => 'Approve\Admin\Controllers',
    'path' => 'approval-management.ApprovalStastic.ApprovalStastic-list',
], function (Router $router) {
    // 列表
    $router->get('approve-statistic/list', 'ApproveStatisticController@index');
    //审批报表
    $router->get('approve-statistic/auidt-list', 'ApproveStatisticController@indexAudit');
    // 审批类型
    $router->get('approve-statistic/type-list', 'ApproveStatisticController@approveTypeList');
    // 审批人
    $router->get('approve-statistic/user-list', 'ApproveStatisticController@approveUserList');
    // 详情
    $router->get('approve-statistic/detail', 'ApproveStatisticController@show');
    // 用户统计汇总
    $router->get('approve-statistic/user-statistic-summary', 'ApproveStatisticController@userStatisticSummary');
});
