<?php

use Admin\Controllers\Export\ExportController;
use Laravel\Lumen\Routing\Router;

/**
 * 推送列表
 *
 * @var Router $router
 */

$router->group([

], function (Router $router) {
    $router->get('export/not-auth-user', ExportController::class . '@notAuthUser');
    $router->get('export/not-sign-order', ExportController::class . '@notSignOrder');
});
