<?php

use Laravel\Lumen\Routing\Router;

/**
 * 借款订单
 *
 * @var Router $router
 */

$router->group([
    'namespace' => 'Admin\Controllers\Order',
    'path' => 'borrowing.borrow',
], function (Router $router) {
    $router->get('order/index', 'OrderController@index');
    $router->get('order/view', 'OrderController@view');
});
