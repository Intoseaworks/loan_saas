<?php

use Api\Controllers\Order\ContractController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([], function (Router $router) {
    // 获取签名认证方digio的链接
    $router->get('/contract/get-sign-url', ContractController::class . '@getSignUrl');
    // 生成电子合同-并上传签名认证方digio
    // $router->post('/contract/generate-contract', ContractController::class . '@generateContract');
    // 获取合同数据
    $router->get('/contract/contract-data', ContractController::class . '@contractData');
    // 获取合同html
    $router->get('/contract/get-contract', ContractController::class . '@getContract');
    // 获取Sanction合同html
    $router->get('/contract/get-sanction-contract', ContractController::class . '@getSanctionContract');
    // 发送电子合同至用户绑定邮箱
    $router->post('/contract/send-contract', ContractController::class . '@sendContract');

    // 签约页面，返回html
    //$router->get('/contract/sign-page', ContractController::class . '@signPage');

    // 签约确认
    //$router->get('/contract/sign-submit', ContractController::class . '@signSubmit');

    // 确认短验
    $router->post('/contract/confirm-opt', ContractController::class . '@confirmOpt');
});

