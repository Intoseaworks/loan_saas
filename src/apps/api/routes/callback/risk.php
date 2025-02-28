<?php

use Api\Controllers\Callback\RiskController;
use Laravel\Lumen\Routing\Router;

/**
 * 印牛服务风控 回调处理
 * @var Router $router
 */
$router->group([], function (Router $router) {
    // 机审结果回调
    $router->post('callback/risk/task_notice', RiskController::class . '@taskNotice');
});

