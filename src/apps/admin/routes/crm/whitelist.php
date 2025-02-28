<?php

use Admin\Controllers\Crm\WhiteListController;
use Laravel\Lumen\Routing\Router;

/**
 * 黑名单用户
 *
 * @var Router $router
 */
$router->group([
    'path' => 'crm-whitelist',
        ], function (Router $router) {
    //白名单上传
    $router->post('crm/whitelist/upload', WhiteListController::class . '@upload');
    //白名单批次状态变更
    $router->post('crm/whitelist/batch-set-status', WhiteListController::class . '@batchSetStatus');
    //白名单状态变更
    $router->post('crm/whitelist/set-status', WhiteListController::class . '@setStatus');
    //白名单延期
    $router->post('crm/whitelist/postpone', WhiteListController::class . '@postpone');
    //白名单批次延期
    $router->post('crm/whitelist/postpone-betch', WhiteListController::class . '@postponeBetch');
    //白名单批次列表
    $router->post('crm/whitelist/index-batch', WhiteListController::class . '@indexBatch');
    //白名单列表
    $router->post('crm/whitelist/index', WhiteListController::class . '@index');
    //字典
    $router->post('crm/whitelist/dict', WhiteListController::class . '@dict');
});
