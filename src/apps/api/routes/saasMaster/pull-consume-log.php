<?php

use Api\Controllers\SaasMaster\PullConsumeLogController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([], function (Router $router) {
    // 拉取订单计费数据
    $router->post('saas-master/consume/pull-order', PullConsumeLogController::class . '@pullOrderData');
    // 拉取机审计费数据
    $router->post('saas-master/consume/pull-system-approve', PullConsumeLogController::class . '@pullSystemApproveData');
});

