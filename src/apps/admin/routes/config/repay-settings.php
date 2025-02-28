<?php

use Admin\Controllers\Config\ConfigController;
use Laravel\Lumen\Routing\Router;

/**
 * 安全设置
 *
 * @var Router $router
 */

$router->group([
    'path' => 'system-settings.repay-settings',
], function (Router $router) {
    $router->post('config/update-repay', ConfigController::class . '@updateRepay');
});
