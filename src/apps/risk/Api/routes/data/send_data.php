<?php

use Risk\Api\Controllers\Data\SendDataController;

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
    // 上传用户数据
    $router->post('send_data/all', SendDataController::class . '@all');
    // 上传公共数据
    $router->post('send_data/common', SendDataController::class . '@common');
});
