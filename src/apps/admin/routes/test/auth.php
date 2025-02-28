<?php

use Admin\Controllers\Test\TestAuthController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */

$router->group([

], function (Router $router) {
    //认证或取消
    $router->get('test/auth/clear-or-complete', TestAuthController::class . '@clearOrComplete');
    //清除用户
    $router->get('test/auth/clear-user', TestAuthController::class . '@clearUser');
});
