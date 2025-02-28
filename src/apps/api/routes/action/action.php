<?php

use Laravel\Lumen\Routing\Router;

/**
 * 事件模块
 * @var Router $router
 */

$router->group([
    'namespace' => 'Api\Controllers\Action',
    'prefix' => 'action',
],
    function (Router $router) {
        $router->post('create', 'ActionLogController@create');
    });

