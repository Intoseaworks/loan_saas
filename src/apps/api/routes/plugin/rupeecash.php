<?php

use Api\Controllers\Login\PwdController;
use Api\Controllers\Data\ThirdDataController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([],
    function (Router $router) {
        $router->post('pwd-login', PwdController::class . '@loginByPwd');
        $router->post('pwd-reg', PwdController::class . '@reg');
        $router->post('change-pwd', PwdController::class . '@changePwd');
        $router->post('retrieve-pwd', PwdController::class . '@retrievePwd');
        $router->post('data/facebook', ThirdDataController::class . '@facebook');
    }
);

