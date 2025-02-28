<?php

use Api\Controllers\Callback\Nbfc\NbfcController;
use Laravel\Lumen\Routing\Router;

/**
 * nbfc回调处理
 * @var Router $router
 */
$router->group([], function (Router $router) {
    // htmlpay支付回调
    $router->post('callback/kudos/notice', NbfcController::class . '@kudosNotice');
});

