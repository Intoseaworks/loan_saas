<?php

use Admin\Controllers\Config\ConfigController;
use Admin\Controllers\Config\BannerController;
use Laravel\Lumen\Routing\Router;

/**
 * 运营商设置
 *
 * @var Router $router
 */

$router->group([
    'path' => 'system-settings.operate-settings',
], function (Router $router) {
    $router->post('config/update-operate', ConfigController::class . '@updateOperate');
    
    $router->post('banner/upload', BannerController::class . '@upload');
    $router->get('banner/index', BannerController::class . '@index');
    $router->post('banner/stop', BannerController::class . '@stop');
    $router->post('banner/start', BannerController::class . '@start');
    $router->post('banner/remove', BannerController::class . '@remove');
});
