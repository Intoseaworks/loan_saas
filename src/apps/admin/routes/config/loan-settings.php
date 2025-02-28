<?php

use Admin\Controllers\Config\LoanMultipleConfigController;
use Admin\Controllers\Config\LoanClmConfigController;
use Laravel\Lumen\Routing\Router;

/**
 * 货款设置
 *
 * @var Router $router
 */
$router->group([
    'path' => 'system-settings.loan-settings',
], function (Router $router) {
    // 查看贷款设置
    $router->get('loan-config/view', LoanMultipleConfigController::class . '@view');
    // 保存贷款设置
    $router->post('loan-config/save', LoanMultipleConfigController::class . '@save');
    // 删除贷款设置项
    $router->post('loan-config/item-del', LoanMultipleConfigController::class . '@itemDel');
    
        // 查看贷款设置
    $router->get('clm-loan-config/view', LoanClmConfigController::class . '@view');
    // 保存贷款设置
    $router->post('clm-loan-config/save', LoanClmConfigController::class . '@save');
    // 删除贷款设置项
    $router->post('clm-loan-config/item-del', LoanClmConfigController::class . '@itemDel');
});
