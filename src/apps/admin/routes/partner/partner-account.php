<?php

use Admin\Controllers\Partner\PartnerAccountController;
use Laravel\Lumen\Routing\Router;

/**
 * 商户账户
 * @var Router $router
 */
$router->group([
    'path' => 'account.recharge_list',
], function (Router $router) {
    // 商户消费记录统计列表
    $router->get('partner-account/consume/list', PartnerAccountController::class . '@consumeList');
    // 消费记录明细列表
    $router->get('partner-account/consume-log/list', PartnerAccountController::class . '@consumeLogList');
    // 商户账户每日统计列表
    $router->get('partner-account/account-statistics/list', PartnerAccountController::class . '@accountStatisticsList');
});
