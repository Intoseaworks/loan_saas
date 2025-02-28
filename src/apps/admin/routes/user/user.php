<?php

use Admin\Controllers\User\UserContactController;
use Laravel\Lumen\Routing\Router;

/**
 * 用户信息
 *
 * @var Router $router
 */

$router->group([
    'path' => 'user-management.user',
], function (Router $router) {
    //添加黑名单
    $router->post('user/create-contact', UserContactController::class . '@createUserContact');
});
