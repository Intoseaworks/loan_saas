<?php

use Admin\Controllers\User\UserController;
use Laravel\Lumen\Routing\Router;

/**
 * 用户信息
 *
 * @var Router $router
 */

$router->group([
    'path' => 'user-management.userlist',
], function (Router $router) {
    //用户列表
    $router->get('user/index', UserController::class . '@index');
    //用户详情
    $router->get('user/view', UserController::class . '@view');
    //添加黑名单
    $router->post('user/add_black', UserController::class . '@addBlack');
    //批量添加黑名单
    $router->post('user/batch-add-black', UserController::class . '@batchAddBlack');
});
