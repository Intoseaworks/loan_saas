<?php

use Laravel\Lumen\Routing\Router;

/**
 * 用户模块
 * @var Router $router
 */

$router->group([
    'namespace' => 'Api\Controllers\Risk',
],
    function (Router $router) {
        $router->post('risk/auth_time', 'RiskController@authTime');
    });

