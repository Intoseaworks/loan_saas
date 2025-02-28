<?php

use Api\Controllers\Callback\AadhaarController;
use Laravel\Lumen\Routing\Router;

/**
 * aadhaarApi 回调处理
 * @var Router $router
 */
$router->group([], function (Router $router) {
    // esign签约回调
    $router->post('callback/aadhaar/esign-response', AadhaarController::class . '@esignResponse');
});

