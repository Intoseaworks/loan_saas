<?php

use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([
    'prefix' => 'menu',
    'path' => 'menu'], function (Router $router) {
    //菜单列表
    $router->get('index', 'MenuController@index');
    //同步菜单
    $router->post('sync-menus', 'ApiController@syncMenus');
});