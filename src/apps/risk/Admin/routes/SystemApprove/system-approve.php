<?php

use Laravel\Lumen\Routing\Router;
use Risk\Admin\Controllers\SystemApprove\SystemApproveConfigController;

/**
 * 机审规则设置
 * @var Router $router
 */

$router->group([
    'path' => 'system-settings.machine-audit-settings',
], function (Router $router) {
    //$router->post('config/system-approve-save', RiskConfigController::class . '@systemApproveSave');
    //$router->get('config/system-approve-view', RiskConfigController::class . '@systemApproveView');

    $router->post('config/system-approve-save', SystemApproveConfigController::class . '@systemApproveSave');
    $router->get('config/system-approve-view', SystemApproveConfigController::class . '@systemApproveView');
});
