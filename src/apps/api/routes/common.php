<?php

use Api\Controllers\Captcha\CaptchaController;
use Api\Controllers\Common\BankcardController;
use Api\Controllers\Common\CallbackController;
use Api\Controllers\Common\ChannelController;
use Api\Controllers\Common\CityController;
use Api\Controllers\Common\CommonController;
use Api\Controllers\Common\ConfigController;
use Api\Controllers\Common\ExceptionController;
use Api\Controllers\Common\XinyanController;
use Api\Controllers\Login\LoginController;
use Api\Controllers\Test\TestController;
use Api\Controllers\Upload\UploadController;
use Api\Controllers\Publicize\PublicizeController;
use Laravel\Lumen\Routing\Router;

/**
 * 公共模块
 * @var Router $router
 */

$router->group([],
    function (Router $router) {
        $router->get('config', ConfigController::class . '@index');
        $router->get('config/option', ConfigController::class . '@option');
        //$router->get('district-code', LocationController::class . '@districtCode');
        $router->post('login', LoginController::class . '@login');
        $router->post('pwd-login', LoginController::class . '@loginByPwd');
        $router->post('register', LoginController::class . '@reg');
        $router->post('register-web', LoginController::class . '@regWeb');
        $router->post('forgot-pwd', LoginController::class . '@forgotPwd');
        $router->post('re-login', LoginController::class . '@reLogin');
        $router->get('logout', LoginController::class . '@logout');
        $router->post('send-sms-code', CaptchaController::class . '@sendSmsCode');

        $router->get('config/loan', ConfigController::class . '@loan');
        $router->get('config/test', TestController::class . '@test');

        //上传
        $router->post('upload', UploadController::class . '@create');
        $router->post('upload-replenish', UploadController::class . '@replenish');
        $router->options('upload', function () {
            header('HTTP/1.1 204 No Content');
        });
        $router->post('upload/card', UploadController::class . '@idCard');
        $router->options('upload/card', function () {
            header('HTTP/1.1 204 No Content');
        });
        //终端异常上报
        $router->post('exception/report', ExceptionController::class . '@report');
        //下载
        $router->get('downloadByType', UploadController::class . '@downloadByType');

        //银行卡相关
        $router->get('bank/bank-list', BankcardController::class . '@getBankList');
        //获取分行卡
        $router->get('bank/bank-branch-list', BankcardController::class . '@getBranch');
        //获取银行城市
        $router->get('bank/bank-city-list', BankcardController::class . '@getBankCity');
        $router->get('bank/bank-list', BankcardController::class . '@getBankList');
        //获取分行卡
        $router->get('bank/bank-branch-list', BankcardController::class . '@getBranch');
        //获取银行城市
        $router->get('bank/bank-city-list', BankcardController::class . '@getBankCity');
        //peso线下放款渠道
        $router->get('bank/institution_list', BankcardController::class . '@getInstitution');
        //peso线上放款渠道
        $router->get('bank/channel_list', BankcardController::class . '@getChannel');
        //卡bin查询
        //$router->post('bankcard/bin', BankcardController::class . '@bin');
        //鉴权绑卡-发送短验构造参数
        //$router->post('bankcard/bind-start', BankcardController::class . '@bindStart');
        //鉴权绑卡-支付系统通知
        $router->post('callback/bank-card-auth-bind-notice', CallbackController::class . '@bankCardAuthBindNotice');

        //渠道统计
        $router->post('channel/record', ChannelController::class . '@record');

        // 新颜跳转url
        $router->get('xinyan/success', XinyanController::class . '@success');


        // 用户详情配置项
        $router->get('config/user-info-config', ConfigController::class . '@userInfo');

        // 地区-城市下拉列表 /city/get-city
        $router->get('city/get-city', CityController::class . '@getCity');
        // 选择 银行的城市列表
        $router->get('city/get-bank-city', CityController::class . '@getBankCity');
        // residential city城市选择 /city/residential-city
        $router->get('city/residential-city', CityController::class . '@residentialCity');
        $router->post('city/position-address', CityController::class . '@positionAddress');

        // 获取版本信息 /version
        $router->get('version', CommonController::class . '@version');

        // 发送手机验证码
        $router->post('captcha/send-sms', CaptchaController::class . '@sendSms');

        // h5发送手机验证码
        $router->post('captcha/send-sms-web', CaptchaController::class . '@sendSmsWeb');

        // 发送邮件验证码
        $router->post('captcha/send-email', CaptchaController::class . '@sendMail');

        // 发送语音验证码
        $router->post('captcha/send-voice', CaptchaController::class . '@sendVoice');

        //上传
        //$router->post('upload', UploadController::class . '@create');
        $router->get('dict', CommonController::class . '@dict');
        //友情链接,推广链接
        $router->get('friend-link', CommonController::class . '@friendLink');
        //app公告
        $router->get('notice', ConfigController::class . '@notice');
        //Banner
        $router->get('banner', PublicizeController::class . '@view');
    });

