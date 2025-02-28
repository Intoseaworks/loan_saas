<?php

use Admin\Controllers\Repayment\RepaymentPlanController;
use Laravel\Lumen\Routing\Router;

/**
 * 已还款订单
 *
 * @var Router $router
 */

$router->group([
    'path' => 'post-management.postManagementlist',
], function (Router $router) {
    //已还款列表
    $router->get('repayment-plan/paid-list', RepaymentPlanController::class . '@paidList');
    //已还款详情
    $router->get('repayment-plan/paid-view', RepaymentPlanController::class . '@paidView');
});
