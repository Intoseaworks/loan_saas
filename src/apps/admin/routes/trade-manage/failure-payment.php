<?php

use Admin\Controllers\TradeManage\RemitController;
use Laravel\Lumen\Routing\Router;

/**
 * 出款失败处理
 *
 * @var Router $router
 */

$router->group([
    'path' => 'payment-management.failurePayment',
], function (Router $router) {
    // 出款失败列表
    $router->get('trade-manage/fail-list', RemitController::class . '@failList');
    // 批量处理
    $router->post('trade-manage/cancel-batch', RemitController::class . '@cancelBatch');
    // 批量流转待放款
    $router->post('trade-manage/to-wait-remit-batch', RemitController::class . '@toWaitRemitBatch');
});
