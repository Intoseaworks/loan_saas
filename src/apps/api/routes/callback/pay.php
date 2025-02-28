<?php

use Api\Controllers\Callback\PayController;
use Laravel\Lumen\Routing\Router;

/**
 * 印牛服务支付 回调处理
 * @var Router $router
 */
$router->group([], function (Router $router) {
    // htmlpay支付回调
    $router->post('callback/html-pay', PayController::class . '@htmlPay');
    // htmlpay支付结果重定向
    $router->get('callback/html-redirect', PayController::class . '@htmlRedirect');
    $router->post('callback/html-redirect', PayController::class . '@htmlRedirect');

    $router->get('callback/return-success', PayController::class . '@returnSuccess');
    $router->get('callback/result-return', PayController::class . '@returnFail');
});

