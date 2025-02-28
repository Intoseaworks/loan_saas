<?php

use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([
    'prefix' => 'operation',
    'path' => 'operation'], function (Router $router) {
    //创建角色
    $router->post('create', 'OperationController@create');
    //编辑角色
    $router->post('edit', 'OperationController@edit');
    //删除角色
    $router->post('delete', 'OperationController@destory');
});