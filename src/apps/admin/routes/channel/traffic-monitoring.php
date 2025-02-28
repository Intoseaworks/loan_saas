<?php

use Admin\Controllers\Channel\ChannelController;
use Laravel\Lumen\Routing\Router;

/**
 * 流量监控
 *
 * @var Router $router
 */

$router->group([
    'path' => 'cooperation-platform.traffic-monitoring',
], function (Router $router) {
    $router->get('channel/monitor', ChannelController::class . '@monitor');
});
