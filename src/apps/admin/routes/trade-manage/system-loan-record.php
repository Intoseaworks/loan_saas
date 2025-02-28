<?php

use Admin\Controllers\TradeManage\TradeLogController;
use Laravel\Lumen\Routing\Router;

/**
 * 系统放款记录
 *
 * @var Router $router
 */

$router->group([
    'path' => 'payment-management.systemLoanRecord',
], function (Router $router) {
    // 系统放款记录
    $router->get('trade-manage/system-pay-list', TradeLogController::class . '@systemPayList');
});
