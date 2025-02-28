<?php

use Api\Controllers\LoanMarket\FxController;
use Laravel\Lumen\Routing\Router;

/**
 * 飞象回调接口
 * @var Router $router
 */
$router->group([
    'prefix' => 'loan-market-fx',
        ],
        function (Router $router) {
    $router->post('accept-user', FxController::class . '@acceptUser');
    $router->post('accept-order', FxController::class . '@acceptOrder');
    $router->post('push-user', FxController::class . '@pushUser');
    $router->post('push-detail', FxController::class . '@pushDetail');
    $router->post('push-contact', FxController::class . '@pushContact');
    $router->post('push-overdue', FxController::class . '@pushOverdue');
    $router->post('create-repayment', FxController::class . '@createRepayment');
    $router->post('query-payout-status', FxController::class . '@queryPayoutStatus');
    $router->post('query-repayment-status', FxController::class . '@queryRepaymentStatus');
    $router->post('query-order', FxController::class . '@queryOrder');
});
