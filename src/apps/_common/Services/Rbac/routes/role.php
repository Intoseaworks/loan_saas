<?php

use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([
    'prefix' => 'role',
    'path' => 'role'], function (Router $router) {
    //角色列表
    $router->get('index', 'RoleController@index');
    //角色详情
    $router->get('show', 'RoleController@show');
    //创建角色
    $router->post('create', 'RoleController@create');
    //编辑角色
    $router->post('edit', 'RoleController@edit');
    //删除角色
    $router->post('delete', 'RoleController@destory');
    //获取所有角色
    $router->get('all-role', 'RoleController@getAllrole');
    //通过角色获取权限
    $router->get('get-permission-by-role', 'RoleController@getPermissionByRole');
    //获取授权人员列表
    $router->get('users', 'RoleController@getRoleUser');
    //未授权角色人员列表
    $router->get('unauthorized-user', 'RoleController@getUnauthorizedUser');
    //用户角色授权
    $router->post('assign-role', 'RoleController@assignRole');
    //清空权限
    $router->post('detach-role', 'RoleController@detachRole');
    //用户角色列表
    $router->get('user-role', 'RoleController@getUserRole');
});