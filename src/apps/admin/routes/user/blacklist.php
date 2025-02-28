<?php

use Admin\Controllers\User\UserBlackController;
use Laravel\Lumen\Routing\Router;

/**
 * 黑名单用户
 *
 * @var Router $router
 */

$router->group([
    'path' => 'user-management.blacklist',
], function (Router $router) {
    //黑名单用户列表
    $router->get('user/black_list', UserBlackController::class . '@index');
    //导入黑名单用户统计
    $router->post('user/black_list_upload', UserBlackController::class . '@upload');
    //导入黑名单用户
    $router->post('user/black_list_confirm', UserBlackController::class . '@confirm');
    //黑名单用户详情
    $router->get('user/black_view', UserBlackController::class . '@view');
    //移除黑名单
    $router->get('user/move_black', UserBlackController::class . '@move');
});
