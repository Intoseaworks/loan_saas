<?php

use Admin\Controllers\Repayment\RepaymentPlanController;
use Laravel\Lumen\Routing\Router;

/**
 * 已坏账订单
 *
 * @var Router $router
 */

$router->group([
    'path' => 'post-management.baddebtorderslist',
], function (Router $router) {
    //已坏账列表
    $router->get('repayment-plan/bad-list', RepaymentPlanController::class . '@badList');
    //已坏账详情
    $router->get('repayment-plan/bad-view', RepaymentPlanController::class . '@badView');

});
