<?php

use Admin\Controllers\TradeManage\RemitController;
use Laravel\Lumen\Routing\Router;

/**
 * 人工出款
 *
 * @var Router $router
 */

$router->group([
    'path' => 'payment-management.artificialPayment',
], function (Router $router) {
    // 人工出款列表
    $router->get('trade-manage/manual-remit-list', RemitController::class . '@manualRemitList');
    // 人工出款详情
    $router->get('trade-manage/manual-remit-detail', RemitController::class . '@manualRemitDetail');
    // 人工出款提交结果
    $router->post('trade-manage/manual-remit-submit', RemitController::class . '@manualRemitSubmit');
    // 人工出款skypay余额
    $router->get('trade-manage/skypay-balance', RemitController::class . '@skypayBalance');
});
