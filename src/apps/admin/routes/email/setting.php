<?php

use Admin\Controllers\Email\SettingController;
use Laravel\Lumen\Routing\Router;

/**
 * CRM用户
 *
 * @var Router $router
 */
$router->group([
    'path' => 'email',
        ], function (Router $router) {
    # 邮件账号配置
    $router->post('email/user/save', SettingController::class . '@save');
    $router->post('email/user/index', SettingController::class . '@index');
    $router->post('email/user/remove', SettingController::class . '@remove');
    $router->get('email/user/get', SettingController::class . '@get');
    
    # 模板配置
    $router->get('email/tpl/type', SettingController::class . '@tplType');
    $router->post('email/tpl/index', SettingController::class . '@tplIndex');
    $router->post('email/tpl/save', SettingController::class . '@tplSave');
    $router->post('email/tpl/remove', SettingController::class . '@tplRemove');
    $router->post('email/tpl/import', SettingController::class . '@tplImport');
    $router->post('email/collection/level', SettingController::class . '@collectionLevel');
});
