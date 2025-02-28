<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

use Laravel\Lumen\Routing\Router;

/**
 * 菜单管理
 *
 * @var Router $router
 */
$router->group([
    'namespace' => 'Common\Services\Rbac\Controllers',
    'prefix' => 'rbac',
    'path' => 'auth.menus',
], function (Router $router) {
    //同步菜单
    $router->post('menu/sync-main-menu', 'MenuController@syncMenu');
});
