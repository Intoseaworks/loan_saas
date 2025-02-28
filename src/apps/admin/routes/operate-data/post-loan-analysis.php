<?php

use Admin\Controllers\OperateData\PostLoanController;
use Laravel\Lumen\Routing\Router;

/**
 * 每日贷后分析
 *
 * @var Router $router
 */

$router->group([
    'path' => 'statistics.operational.afterloan_list',
], function (Router $router) {
    $router->get('post-loan/list', PostLoanController::class . '@index');
    $router->get('post-loan/list', PostLoanController::class . '@index');
    // 复贷率
    $router->get('post-loan/reloan-list', PostLoanController::class . '@reloanRate');
});
