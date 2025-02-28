<?php

use Admin\Controllers\Repayment\RepaymentPlanController;
use Laravel\Lumen\Routing\Router;

/**
 * 还款计划
 *
 * @var Router $router
 */

$router->group([
    'path' => 'repayment-management.repaymentlist',
], function (Router $router) {
    //还款计划列表
    $router->get('repayment-plan/list', RepaymentPlanController::class . '@index');
    //还款计划详情
    $router->get('repayment-plan/detail', RepaymentPlanController::class . '@view');
});
