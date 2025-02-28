<?php

use Admin\Controllers\Partner\PartnerRechargeController;
use Laravel\Lumen\Routing\Router;

/**
 * 充值记录
 *
 * @var Router $router
 */

$router->group([
//    'namespace' => 'Admin\Controllers\Partner',
//    'path' => 'borrowing.loan',
], function (Router $router) {
    /** 充值列表 */
    $router->get('partner-recharge/list', PartnerRechargeController::class . '@rechargeList');

    /** 充值申请 */
    $router->post('partner-recharge/apply', PartnerRechargeController::class . '@rechargeApply');

});
