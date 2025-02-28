<?php

use Api\Controllers\Callback\DigioController;
use Laravel\Lumen\Routing\Router;

/**
 * digio 回调处理
 * @var Router $router
 */
$router->group([], function (Router $router) {
    // 签名认证回调
    $router->get('/callback/digio/sign-callback', DigioController::class . '@signCallback');
    $router->post('/callback/digio/sign-callback', DigioController::class . '@signCallback');
    // return fail;
    $router->get('/callback/digio/fail', DigioController::class . '@returnFail');
    // return success;
    $router->get('/callback/digio/success', DigioController::class . '@returnSuccess');
});

