<?php

use Api\Controllers\User\BankcardController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([],
    function (Router $router) {
        // 查找IFSC /bankcard/look-up-ifsc
        $router->get('bankcard/look-up-ifsc', BankcardController::class . '@lookUpIfsc');
        // 查找银行 /bankcard/get-branch
        $router->get('bankcard/get-branch', BankcardController::class . '@getBranch');
        // 绑定银行卡 /bankcard/create
        $router->post('bankcard/create', BankcardController::class . '@create');
        // 银行卡首页 /bankcard/index
        $router->post('bankcard/index', BankcardController::class . '@index');
    }
);

