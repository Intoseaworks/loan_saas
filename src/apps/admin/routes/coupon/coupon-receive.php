<?php

use Admin\Controllers\Coupon\CouponReceiveController;
use Laravel\Lumen\Routing\Router;

/**
 * 优惠券领用使用
 *
 * @var Router $router
 */
$router->group([
    'path' => 'coupon-coupon.receive',
        ], function (Router $router) {
    //优惠券领用使用列表
    $router->post('coupon/coupon-receive/index', CouponReceiveController::class . '@index');
    //优惠券领用使用列表自定义发送
    $router->post('coupon/coupon-receive/index-custom', CouponReceiveController::class . '@indexCustom');
    //优惠券领用使用详情
    $router->post('coupon/coupon-receive/view', CouponReceiveController::class . '@view');
    //添加优惠券领用使用
    $router->post('coupon/coupon-receive/add-coupon-receive', CouponReceiveController::class . '@addCouponReceive');
    //优惠券领用使用编辑
    $router->post('coupoon/coupon-receive/set-coupon-receive', CouponReceiveController::class . '@setCouponReceive');
});
