<?php

use Admin\Controllers\ChannelStatistics\ChannelStatisticsController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */

$router->group([
    'path' => 'statistics.daily-flow-statistics'
], function (Router $router) {
    //模块列表
    $router->get('channel-statistics/index', ChannelStatisticsController::class . '@index');
    //模块详情
    $router->get('channel-statistics/view', ChannelStatisticsController::class . '@view');
});
