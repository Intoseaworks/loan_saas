<?php

use Admin\Controllers\Notice\NoticeController;
use Laravel\Lumen\Routing\Router;

/**
 * 短信列表
 *
 * @var Router $router
 */

$router->group([
    'path' => 'inform-management.notelist',
], function (Router $router) {
    //短信管理
    $router->get('notice/sms-list', NoticeController::class . '@smsList');
    //保存SMS任务
    $router->post('notice/sms-task', NoticeController::class . '@smsTask');
    //查看任务
    $router->get('notice/sms-task/view', NoticeController::class . '@smsTaskView');
    //SMS任务列表
    $router->get('notice/sms-task-list', NoticeController::class . '@smsTaskList');
});
