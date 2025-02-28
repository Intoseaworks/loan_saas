<?php

use Admin\Controllers\User\UserController;
use Laravel\Lumen\Routing\Router;

/**
 * 用户反馈
 *
 * @var Router $router
 */

$router->group([
    'path' => 'user-management.feedbacklist',
], function (Router $router) {
    //反馈列表
    $router->get('user/feedback_list', UserController::class . '@feedbackList');
});
