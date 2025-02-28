<?php

use Admin\Controllers\Activity\ActivityAwardController;
use Laravel\Lumen\Routing\Router;

/**
 * 活动奖励任务
 *
 * @var Router $router
 */
$router->group([
    'path' => 'activity-activity.award',
        ], function (Router $router) {
    //活动奖励列表
    $router->post('activity/activity-award/index', ActivityAwardController::class . '@index');
    //新建活动奖品下拉列表
    $router->post('activity/activity-award/all', ActivityAwardController::class . '@all');
    //活动奖励详情
    $router->post('activity/activity-award/view', ActivityAwardController::class . '@view');
    //添加活动奖励
    $router->post('activity/activity-award/add-activity-award', ActivityAwardController::class . '@addActivityAward');
    //试算活动奖励发放数量
    $router->post('activity/activity-award/precount-activity-award', ActivityAwardController::class . '@precountActivityAward');
    //活动奖励编辑
    $router->post('activity/activity-award/set-activity-award', ActivityAwardController::class . '@setActivityAward');
});
