<?php

use Admin\Controllers\Risk\RiskBlacklistController;
use Laravel\Lumen\Routing\Router;

/**
 * 风控黑名单
 *
 * @var Router $router
 */

$router->group([
    'path' => 'risk-management.blacklist',
], function (Router $router) {
    //风控黑名单列表
    $router->get('risk-blacklist/index', RiskBlacklistController::class . '@index');
    //风控黑名单详情
    $router->get('risk-blacklist/detail', RiskBlacklistController::class . '@detail');
    //线下导入导入外部黑名单
    $router->post('risk-blacklist/import', RiskBlacklistController::class . '@import');
});
