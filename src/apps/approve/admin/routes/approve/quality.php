<?php

use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([
    'namespace' => 'Approve\Admin\Controllers',
    'path' => 'approval-management.ApprovalManagement.ApprovalQaList-list',
], function (Router $router) {
    // 列表
    $router->get('approve-quality/list', 'ApproveQualityController@index');
    // 审批结果
    $router->get('approve-quality/approve-result-list', 'ApproveQualityController@approveResultList');
    // 质检状态
    $router->get('approve-quality/quality-status-list', 'ApproveQualityController@qualityStatusList');
    // 详情
    $router->get('approve-quality/detail', 'ApproveQualityController@show');
    // 质检结果选项
    $router->get('approve-quality/quality-result-list', 'ApproveQualityController@qualityResultList');
    // 质检提交
    $router->post('approve-quality/quality-submit', 'ApproveQualityController@qualitySubmit');
});
