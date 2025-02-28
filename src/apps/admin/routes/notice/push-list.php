<?php

use Admin\Controllers\Notice\NoticeController;
use Laravel\Lumen\Routing\Router;

/**
 * 推送列表
 *
 * @var Router $router
 */

$router->group([
    'path' => 'inform-management.pushlist',
], function (Router $router) {
    // 推送列表
    $router->get('notice/inbox-list', NoticeController::class . '@inboxList');
});
