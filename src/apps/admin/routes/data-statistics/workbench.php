<?php

use Admin\Controllers\DataStatistics\Workbench\WorkbenchController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */

$router->group([
    'path' => 'work_bench',
], function (Router $router) {
    //控制台首页
    $router->get('data_statistics/workbench/index', WorkbenchController::class . '@index');
    $router->get('data_statistics/workbench/line', WorkbenchController::class . '@line');
    $router->get('data_statistics/workbench/search', WorkbenchController::class . '@search');
});
