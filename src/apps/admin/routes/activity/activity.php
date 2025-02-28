<?php

use Admin\Controllers\Activity\ActivityController;
use Laravel\Lumen\Routing\Router;

/**
 * 优惠券
 *
 * @var Router $router
 */
$router->group([
    'path' => 'activity-activity',
        ], function (Router $router) {
    //优惠券列表
    $router->post('activity/activity/index', ActivityController::class . '@index');
    //新建优惠券任务下拉列表
    $router->post('activity/activity/all', ActivityController::class . '@all');
    //优惠券详情
    $router->post('activity/activity/view', ActivityController::class . '@view');
    //添加优惠券
    $router->post('activity/activity/add-activity', ActivityController::class . '@addActivity');
    //优惠券编辑
    $router->post('activity/activity/set-activity', ActivityController::class . '@setActivity');
});
