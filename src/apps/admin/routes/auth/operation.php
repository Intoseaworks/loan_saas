<?php

use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([
    'namespace' => 'Common\Services\Rbac\Controllers',
    'prefix' => 'rbac',
    'path' => 'auth.menus',
], function (Router $router) {
    //创建功能
    $router->post('operation/create', 'OperationController@create');
    //编辑功能
    $router->post('operation/edit', 'OperationController@edit');
    //删除功能
    $router->post('operation/delete', 'OperationController@destory');
});
