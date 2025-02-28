<?php

use Admin\Controllers\TradeManage\TradeLogController;
use Laravel\Lumen\Routing\Router;

/**
 * 还款管理
 *
 * @var Router $router
 */

$router->group([
    'path' => 'repayment-management.withholdRefundRecord',
], function (Router $router) {
    //代扣还款记录
    $router->get('trade-log/system-repay-list', TradeLogController::class . '@systemRepayList');
});
