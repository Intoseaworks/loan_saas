<?php

use Admin\Controllers\Config\ConfigController;
use Admin\Controllers\Config\SmsTplController;
use Laravel\Lumen\Routing\Router;

/**
 * 安全设置
 *
 * @var Router $router
 */

$router->group([
    'path' => 'system-settings.security-settings',
], function (Router $router) {
    $router->post('config/update-safe', ConfigController::class . '@updateSafe');
    $router->post('config/update-app', ConfigController::class . '@updateApp');
    $router->get('config/get-city', ConfigController::class . '@getCity');

    $router->post('sms-tpl/import', SmsTplController::class . '@import');
    $router->post('sms-tpl/load', SmsTplController::class . '@load');
    $router->post('sms-tpl/save', SmsTplController::class . '@save');
    $router->post('sms-tpl/type-list', SmsTplController::class . '@typeList');
    $router->get('sms-tpl/list', SmsTplController::class . '@list');
});
