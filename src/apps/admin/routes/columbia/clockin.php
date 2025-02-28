<?php

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

use Laravel\Lumen\Routing\Router;

/**
 * 待审批订单
 *
 * @var Router $router
 */
$router->group([
    'namespace' => 'Admin\Controllers\Columbia',
    'path' => 'col.clockin',
], function (Router $router) {
    // 待审批列表
    $router->get('col/clockin_index', 'ClockinController@index');
    $router->get('col/clockin_detail', 'ClockinController@detail');
    
    $router->get('col/clockin_option', 'ClockinController@option');
    $router->post('col/clockin_approve', 'ClockinController@approve');
});
