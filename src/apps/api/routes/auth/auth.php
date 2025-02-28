<?php

use Api\Controllers\Auth\AuthController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */

$router->group([
],
    function (Router $router) {
        $router->get('auth/index', AuthController::class . '@index');
        $router->post('auth/ocr', AuthController::class . '@ocr');
        $router->post('auth/check-card', AuthController::class . '@checkCard');
        $router->post('auth/face', AuthController::class . '@face');
        //$router->post('auth/facebook', AuthController::class . '@facebook');
        $router->post('auth/aadhaar-kyc', AuthController::class . '@aadhaarKYC');
        $router->post('auth/skip', AuthController::class . '@skip');
        
        # 未认证列表
        $router->get('auth/no-auth-list', AuthController::class . '@noAuthList');
    });

