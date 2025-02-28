<?php

use Laravel\Lumen\Routing\Router;

/**
 * 催收率统计
 *
 * @var Router $router
 */

$router->group([
    'namespace' => 'Admin\Controllers\CollectionStatistics',
    'path' => 'statistics.urgeRegain-statistics.reclaimaterate',
], function (Router $router) {
    //催回率统计列表
    $router->get('collection-statistics/rate-list', 'CollectionStatisticsController@rateList');
});
