<?php

use Admin\Controllers\Partner\PartnerBalanceController;
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
    /** 余额查询*/
    $router->get('partner-balance/query', PartnerBalanceController::class . '@balance');

});
