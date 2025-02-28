<?php

use Admin\Controllers\Config\ConfigController;
use Laravel\Lumen\Routing\Router;
use Admin\Controllers\Collection\CollectionCallController;

/**
 * @var Router $router
 */
//全局配置（免登录）
$router->get('config/info', ConfigController::class . '@info');
$router->get('git-update', ConfigController::class . '@gitUpdate');
$router->get('collection/call-setting/merchants', CollectionCallController::class . '@merchants');
$router->get('collection/call-setting/file-index', CollectionCallController::class . '@callFileIndex');
$router->get('collection/call-setting/file', CollectionCallController::class . '@callFile');
//$router->get('config-option/info', ConfigController::class . '@option');
