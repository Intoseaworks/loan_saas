<?php

use Api\Controllers\Order\OrderController;
use Laravel\Lumen\Routing\Router;

/**
 * 用户模块
 * @var Router $router
 */

$router->group([

], function (Router $router) {
    $router->get('order/index', OrderController::class . '@index');
    $router->get('order/last-order', OrderController::class . '@lastOrder');
    $router->get('order/detail', OrderController::class . '@detail');
    $router->post('order/create', OrderController::class . '@create');
//    $router->post('order/replenish', OrderController::class . '@replenish');
    $router->post('order/sign', OrderController::class . '@sign');
    $router->post('order/cancel', OrderController::class . '@cancel');
    $router->post('order/calculate', OrderController::class . '@calculate');
    $router->get('order/agreement', OrderController::class . '@agreement');
    $router->get('order/config', OrderController::class . '@config');
    $router->post('order/update', OrderController::class . '@update');
    $router->get('order/repayment-plan', OrderController::class . '@repaymentPlan');
    $router->get('order/reduction', OrderController::class . '@orderReduction');
});

