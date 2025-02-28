<?php

use Laravel\Lumen\Routing\Router;
use Api\Controllers\Cloud\DataController;

$router->group([], function (Router $router) {
    // 黑名单验证接口
    $router->post('/risk/data', DataController::class . '@get');
});

