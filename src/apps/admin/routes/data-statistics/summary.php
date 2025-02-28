<?php

use Admin\Controllers\DataStatistics\SummaryController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */

$router->group([
    'path' => 'summary.index',
], function (Router $router) {
    //每日流量效果
    $router->get('data_statistics/summary/index', SummaryController::class . '@index');
});
