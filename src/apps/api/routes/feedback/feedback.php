<?php

use Laravel\Lumen\Routing\Router;

/**
 * 反馈模块
 * @var Router $router
 */

$router->group([
    'namespace' => 'Api\Controllers\Feedback',
    'prefix' => 'feedback',
],
    function (Router $router) {
        $router->post('add', 'FeedbackController@add');
        $router->get('faq/index', 'FeedbackFaqController@index');
        $router->get('faq/detail', 'FeedbackFaqController@detail');
    });

