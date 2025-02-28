<?php

use Admin\Controllers\Config\ConfigController;
use Laravel\Lumen\Routing\Router;

/**
 * 审批设置
 *
 * @var Router $router
 */

$router->group([
    'path' => 'system-settings.approval-settings',
], function (Router $router) {
    $router->post('config/update-approve', ConfigController::class . '@updateApprove');
});
