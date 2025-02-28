<?php

use Admin\Controllers\Api\DataStatistics\DataStatisticsController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */

$router->group([

], function (Router $router) {
    $router->post('api/data-statistics/line', DataStatisticsController::class . '@line');
});
