<?php

use Admin\Controllers\TradeManage\TradeLogController;
use Laravel\Lumen\Routing\Router;

/**
 * 支付记录
 *
 * @var Router $router
 */

$router->group([
    'path' => 'payment-management.paymentRecord',
], function (Router $router) {
    // 支付记录列表
    $router->get('trade-manage/trade-log-list', TradeLogController::class . '@tradeLogList');
    // 账户列表
    $router->get('trade-manage/account-list', TradeLogController::class . '@accountList');
    // 账户添加
    $router->post('trade-manage/account-create', TradeLogController::class . '@accountCreate');
    // 禁用/启用 账户
    $router->post('trade-manage/account-disable-or-enable', TradeLogController::class . '@accountDisableOrEnable');
    // 设置默认 账户
    $router->post('trade-manage/account-default', TradeLogController::class . '@accountDefault');
    // 检查账户
    $router->post('trade-manage/account-check', TradeLogController::class . '@accountCheck');
    // 账户列表
    $router->get('trade-manage/account-option', TradeLogController::class . '@accountOption');
});
