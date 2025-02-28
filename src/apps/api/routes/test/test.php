<?php

use Laravel\Lumen\Routing\Router;

/**
 * 测试模块
 * @var Router $router
 */

$router->group([
    'namespace' => 'Api\Controllers\Test',
    'prefix' => 'test',
],
    function (Router $router) {
        $router->get('test', 'TestController@test');
    });

