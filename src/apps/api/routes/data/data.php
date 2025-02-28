<?php

use Api\Controllers\Data\DataController;
use Laravel\Lumen\Routing\Router;

/**
 * 反馈模块
 * @var Router $router
 */

$router->group([
    'prefix' => 'data',
],
    function (Router $router) {
        $router->post('user-contact-count', DataController::class . '@userContactCount');
        // 通讯录上传风控
        $router->post('user-contact', DataController::class.'@userContact');
        // 位置信息上传风控
        $router->post('user-position', DataController::class.'@userPosition');
        // 短信记录上传风控，获取不到sms
        $router->post('user-sms', DataController::class . '@userSms');
        // APP列表上传风控
        $router->post('user-app-list', DataController::class.'@userAppList');
        // 硬件信息上传风控
        $router->post('user-phone-hardware', DataController::class.'@userPhoneHardware');
        // googleToken更新
        $router->post('update-google-token', DataController::class . '@updateGoogleToken');
        // 上传身份证
//        $router->post('upload-id-card', DataController::class.'@uploadIDCard');
        // 身份信息确认
//        $router->post('id-confirm', DataController::class.'@IDConfirm');
        // 身份认证校验
//        $router->get('identity', DataController::class . '@identityList');
        // 上传人脸识别图
//        $router->post('upload-faces', DataController::class.'@uploadFaces');
        // 银行卡信息保存
//        $router->post('bankcard-create', DataController::class . '@bankcardCreate');
        // 手机陀螺仪信息上传
        $router->post('user-gyroscope', DataController::class . '@userGyroscope');
        // 手机照片exif信息上传
        $router->post('user-photo-exif', DataController::class . '@userPhotoExif');
        // 行为埋点数据上报
        $router->post('user-behavior', DataController::class . '@userBehavior');
        // 通讯记录
        $router->post('user-contact-record', DataController::class . '@userContactRecord');
    });

