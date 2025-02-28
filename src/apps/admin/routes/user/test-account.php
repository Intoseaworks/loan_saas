<?php

use Admin\Controllers\User\TestAccountController;
use Laravel\Lumen\Routing\Router;

/**
 * 测试用户
 * @var Router $router
 */

$router->group([
    'path' => 'user-management.testaccount',
], function (Router $router) {
    // 测试账号列表
    $router->get('test-account/list', TestAccountController::class . '@list');
    // 关键字查找用户
    $router->get('test-account/find-user', TestAccountController::class . '@findUser');
    // 添加测试用户
    $router->post('test-account/add', TestAccountController::class . '@add');
    // 查看测试用户详情
    $router->get('test-account/detail', TestAccountController::class . '@detail');
    // 测试用户控制面板
    $router->get('test-account/control-panel', TestAccountController::class . '@controlPanel');
});
