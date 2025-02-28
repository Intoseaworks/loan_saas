<?php

use Admin\Controllers\Coupon\CouponTaskController;
use Laravel\Lumen\Routing\Router;

/**
 * 优惠券任务
 *
 * @var Router $router
 */
$router->group([
    'path' => 'coupon-coupon.task',
        ], function (Router $router) {
    //优惠券列表
    $router->post('coupon/coupon-task/index', CouponTaskController::class . '@index');
    //优惠券详情
    $router->post('coupon/coupon-task/view', CouponTaskController::class . '@view');
    //添加优惠券
    $router->post('coupon/coupon-task/add-coupon-task', CouponTaskController::class . '@addCouponTask');
    //添加优惠券自定义发送
    $router->post('coupon/coupon-task/add-coupon-task-custom', CouponTaskController::class . '@addCouponTaskCustom');
    //试算优惠券发放数量
    $router->post('coupon/coupon-task/precount-coupon-task', CouponTaskController::class . '@precountCouponTask');
    //优惠券编辑
    $router->post('coupoon/coupon-task/set-coupon-task', CouponTaskController::class . '@setCouponTask');
});
