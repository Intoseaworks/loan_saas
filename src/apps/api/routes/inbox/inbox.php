<?php

use Laravel\Lumen\Routing\Router;

/**
 * 私信模块
 * @var Router $router
 */

$router->group([
    'namespace' => 'Api\Controllers\Inbox',
    'prefix' => 'inbox',
],
    function (Router $router) {
        $router->get('index', 'InboxController@index');
        $router->get('get', 'InboxController@get');
        $router->get('set-read', 'InboxController@setRead');
    });

