<?php

use Risk\Api\Controllers\Task\TaskController;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */
$router->group([], function ($router) {
    // 创建任务
    $router->post('task/start_task', TaskController::class . '@startTask');
    // 执行机审&验证数据上传
    $router->post('task/exec_task', TaskController::class . '@execTask');
});
