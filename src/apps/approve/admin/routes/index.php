<?php

use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([
    'middleware' => ['setGuard:admin', 'auth:admin'],
], function (Router $router) {
    //公共接口
    require 'common.php';

    $router->group([
        'middleware' => ['RBAC'],
        'path' => 'admin',
        'prefix' => 'api/approve',
    ], function (Router $router) {
        require 'approve/first-approve.php';
        require 'approve/call-approve.php';
        require 'approve/check.php';
        require 'approve/quality.php';
        require 'approve/statistic.php';
    });
});
