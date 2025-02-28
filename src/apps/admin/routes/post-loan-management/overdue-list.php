<?php

use Admin\Controllers\Repayment\RepaymentPlanController;
use Laravel\Lumen\Routing\Router;

/**
 * 已逾期订单
 *
 * @var Router $router
 */

$router->group([
    'path' => 'post-management.overdueOrderslist',
], function (Router $router) {
    //已逾期列表
    $router->get('repayment-plan/overdue-list', RepaymentPlanController::class . '@overdueList');
    //已逾期详情
    $router->get('repayment-plan/overdue-view', RepaymentPlanController::class . '@overdueView');
});
