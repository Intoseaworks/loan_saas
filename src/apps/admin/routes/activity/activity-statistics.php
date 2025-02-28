<?php

use Admin\Controllers\Activity\ActivityStatisticsController;
use Laravel\Lumen\Routing\Router;

/**
 * 活动统计
 *
 * @var Router $router
 */
$router->group([
    'path' => 'activity-activity.statistics',
        ], function (Router $router) {
    //中奖列表
    $router->post('activity/activity-statistics/index', ActivityStatisticsController::class . '@index');
    //中奖列表导出
    $router->post('activity/activity-statistics/export', ActivityStatisticsController::class . '@export');
    //邀请好友数据统计导出
    $router->post('activity/activity-statistics/export-invite', ActivityStatisticsController::class . '@exportInvite');
    //中奖详情
    $router->post('activity/activity-statistics/view', ActivityStatisticsController::class . '@view');
    //添加中奖记录
    $router->post('activity/activity-statistics/add-activity-statistics', ActivityStatisticsController::class . '@addActivityStatistics');
    //中奖记录编辑
    $router->post('activity/activity-statistics/set-activity-statistics', ActivityStatisticsController::class . '@setActivityStatistics');
});
