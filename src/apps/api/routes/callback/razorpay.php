<?php

use Api\Controllers\Callback\RazorpayController;
use Laravel\Lumen\Routing\Router;

/**
 * 印牛服务支付 回调处理
 * @var Router $router
 */
$router->group([], function (Router $router) {
    // htmlpay支付回调
    $router->post('/callback/razorpay', RazorpayController::class . '@webhook');
    $router->get('/callback/rzptest', RazorpayController::class . '@manual');
});

