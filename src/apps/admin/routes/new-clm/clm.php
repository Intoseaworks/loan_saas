<?php

use Laravel\Lumen\Routing\Router;
use Admin\Controllers\NewClm\NewClmController;

/**
 * @var Router $router
 */

$router->group([
], function (Router $router) {
    // 获取等级金额配置
    $router->get('clm/clm-amount-config', NewClmController::class . '@getClmAmountConfig');
    // 新增等级金额配置
    $router->post('clm/add-clm-amount-config', NewClmController::class . '@addClmAmountConfig');
    // 编辑等级金额配置
    $router->post('clm/edit-clm-amount-config', NewClmController::class . '@editClmAmountConfig');
    // 删除等级金额配置
    $router->post('clm/del-clm-amount-config', NewClmController::class . '@delClmAmountConfig');
    // 获取初始化等级配置
    $router->get('clm/init-level-config', NewClmController::class . '@getInitLevelConfig');
    // 修改初始化等级配置
    $router->post('clm/edit-init-level-config', NewClmController::class . '@edieInitLevelConfig');
});
