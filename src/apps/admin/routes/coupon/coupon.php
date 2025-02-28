<?php

use Admin\Controllers\Coupon\CouponController;
use Laravel\Lumen\Routing\Router;

/**
 * 优惠券
 *
 * @var Router $router
 */
$router->group([
    'path' => 'coupon-coupon',
        ], function (Router $router) {
    //优惠券列表
    $router->post('coupon/coupon/index', CouponController::class . '@index');
    //新建优惠券任务下拉列表
    $router->post('coupon/coupon/all', CouponController::class . '@all');
    //优惠券详情
    $router->post('coupon/coupon/view', CouponController::class . '@view');
    //添加优惠券
    $router->post('coupon/coupon/add-coupon', CouponController::class . '@addCoupon');
    //优惠券编辑
    $router->post('coupoon/coupon/set-coupon', CouponController::class . '@setCoupon');
});
