<?php

use Api\Controllers\SaasMaster\MerchantController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([], function (Router $router) {
    // 初始化商户(已创建)
    $router->post('saas-master/init-merchant', MerchantController::class . '@initMerchant');
    // 创建+初始化商户
    $router->post('saas-master/create-init-merchant', MerchantController::class . '@createInitMerchant');
    // 修改商户密码
    $router->post('saas-master/upd-password', MerchantController::class . '@updPassword');
    // 获取超级管理员信息
    $router->get('saas-master/get-super-admin', MerchantController::class . '@getSuperAdmin');
});

