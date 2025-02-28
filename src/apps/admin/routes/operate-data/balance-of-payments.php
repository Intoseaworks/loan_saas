<?php

use Admin\Controllers\OperateData\BalanceOfPaymentsController;
use Laravel\Lumen\Routing\Router;

/**
 * 每日收支分析
 *
 * @var Router $router
 */

$router->group([
    'path' => 'statistics.operational.budget_list',
], function (Router $router) {
    // 每日收支分析列表
    $router->get('balance-of-repayments/list', BalanceOfPaymentsController::class . '@index');
    // 每日收入分析详情列表
    $router->get('balance-of-repayments/income-list', BalanceOfPaymentsController::class . '@incomeList');
    // 每日支出分析详情列表
    $router->get('balance-of-repayments/disburse-list', BalanceOfPaymentsController::class . '@disburseList');
});
