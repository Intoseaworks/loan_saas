<?php

use Laravel\Lumen\Routing\Router;

/**
 * 催收订单统计
 *
 * @var Router $router
 */

$router->group([
    'namespace' => 'Admin\Controllers\CollectionStatistics',
    'path' => 'statistics.urgeRegain-statistics.urgeregainorder',
], function (Router $router) {
    //催收订单统计列表
    $router->get('collection-statistics/list', 'CollectionStatisticsController@list');
});
