<?php

use Admin\Controllers\Test\TestOrderController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */

$router->group([

], function (Router $router) {
    //订单流转
    $router->get('test/order/status-update', TestOrderController::class . '@statusUpdate');
    //订单取消
    $router->get('test/order/cancel', TestOrderController::class . '@cancel');
    //订单逾期
    $router->get('test/order/overdue', TestOrderController::class . '@overdue');
    //自动创建订单，并走到某一步
    $router->get('test/order/status-create', TestOrderController::class . '@statusCreate');
});
