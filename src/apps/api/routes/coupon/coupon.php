<?php

use Laravel\Lumen\Routing\Router;

/**
 * 优惠券模块
 * @var Router $router
 */
$router->group([
    'namespace' => 'Api\Controllers\Coupon',
    'prefix' => 'coupon',
        ],
        function (Router $router) {
    $router->post('checking', 'CouponController@checking');
    $router->post('checking-after-ordersign', 'CouponController@checkingOrderSign');
    $router->post('my-coupon', 'CouponController@myCoupon');
    $router->post('my-effective-coupon', 'CouponController@myEffectiveCoupon');

});

