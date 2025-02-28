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
 * 角色列表
 *
 * @var Router $router
 */
$router->group([
    'namespace' => 'Common\Services\Rbac\Controllers',
    'prefix' => 'rbac',
    'path' => 'auth.role',
], function (Router $router) {
    //角色列表
    $router->get('role/index', 'RoleController@index');
    //角色详情
    $router->get('role/show', 'RoleController@show');
    //创建角色
    $router->post('role/create', 'RoleController@create');
    //编辑角色
    $router->post('role/edit', 'RoleController@edit');
    //删除角色
    $router->post('role/delete', 'RoleController@destory');
    //通过角色获取权限
    $router->get('role/get-permission-by-role', 'RoleController@getPermissionByRole');
    //获取授权人员列表
    $router->get('role/users', 'RoleController@getRoleUser');
    //未授权角色人员列表
    $router->get('role/unauthorized-user', 'RoleController@getUnauthorizedUser');
    //清空权限
    $router->post('role/detach-role', 'RoleController@detachRole');
    //模块列表
    $router->get('role/guard-list', 'RoleController@getGuardList');
});
