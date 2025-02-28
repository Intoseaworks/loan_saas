<?php

use Admin\Controllers\Repayment\ManualRepaymentController;
use Laravel\Lumen\Routing\Router;

/**
 * 人工还款
 *
 * @var Router $router
 */

$router->group([
    'path' => 'repayment-management.artificiallist',
], function (Router $router) {
    //人工还款列表
    $router->get('manual-repayment/index', ManualRepaymentController::class . '@index');
    //人工还款详情
    $router->get('manual-repayment/detail', ManualRepaymentController::class . '@detail');
    // 动态计算逾期费用
    $router->get('manual-repayment/calc-overdue', ManualRepaymentController::class . '@calcOverdue');
    // 提交人工还款
    $router->post('manual-repayment/repay-submit', ManualRepaymentController::class . '@repaySubmit');
    // 收款账户列表
    $router->get('manual-repayment/admin-trade-repay-account-list',
        ManualRepaymentController::class . '@adminTradeRepayAccountList');
    // 添加催收记录
    $router->post('manual-repayment/collection_submit', ManualRepaymentController::class . '@collectionSubmit');
    // 催收记录列表
    $router->get('manual-repayment/collection_record_list', ManualRepaymentController::class . '@collectionRecordList');
});
