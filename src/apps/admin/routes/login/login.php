<?php

use Admin\Controllers\Login\LoginController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */

$router->group([
], function (Router $router) {
    //账密登录
    $router->post('login/pwd_login', LoginController::class . '@pwdLogin');
    //钉钉登录
    $router->get('login/ding_login', LoginController::class . '@dingLoginView');
    $router->get('login/ding_login_back', LoginController::class . '@dingLoginBack');
    $router->get('login/ding_login_test', LoginController::class . '@dingTest');
});
