<?php

use Admin\Controllers\Crm\CustomerController;
use Laravel\Lumen\Routing\Router;

/**
 * CRM用户
 *
 * @var Router $router
 */
$router->group([
    'path' => 'crm-customer',
        ], function (Router $router) {
    //客户列表
    $router->post('crm/customer/index', CustomerController::class . '@index');
    //客户详情
    $router->post('crm/customer/view', CustomerController::class . '@view');
    //添加Fb
    $router->post('crm/customer/add-fb', CustomerController::class . '@addFb');
    //添加手机号
    $router->post('crm/customer/add-telephone', CustomerController::class . '@addTelephone');
    //添加email
    $router->post('crm/customer/add-email', CustomerController::class . '@addEmail');
    //添加证件
    $router->post('crm/customer/add-paper', CustomerController::class . '@addPaper');
    
    //客户详情
    $router->post('crm/customer/set-main-telephone', CustomerController::class . '@setMainTelephone');
    //启用禁用
    $router->post('crm/customer/set-telephone-status', CustomerController::class . '@setTelephoneStatus');
    //字典
    $router->post('crm/customer/dict', CustomerController::class . '@dict');
});
