<?php

use Api\Controllers\Order\RenewalController;
use Laravel\Lumen\Routing\Router;

/**
 * 续期
 * @var Router $router
 */
$router->group([], function (Router $router) {
    // 获取续期信息
    $router->get('renewal/get-renewal-info', RenewalController::class . '@getRenewalInfo');
    // 确认续期
    $router->post('renewal/confirm-renewal', RenewalController::class . '@confirmRenewal');
    //申请续期
    $router->post('renewal/apply-renewal', RenewalController::class . '@applyRenewal');
});

