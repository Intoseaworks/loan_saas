<?php

use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */

$router->group([
    'prefix' => 'app',
    'middleware' => ['setMerchant:app', "responseMiddleware:app"],
], function (Router $router) {
    $router->group([
    ], function (Router $router) {
        // 行为埋点数据上报未登录前
        $router->post('data/user-behavior-notoken', \Api\Controllers\Data\DataController::class . '@userBehaviorNotoken');
        //jwt test
        $router->post('login_test', \Api\Controllers\Login\LoginController::class . '@loginTest');
        $router->get('test', \Api\Controllers\Login\LoginController::class . '@test');


        require 'common.php';
        require 'user/user.php';
        require 'feedback/feedback.php';
        require 'inbox/inbox.php';
        require 'action/action.php';
        require 'test/test.php';
        require 'data/data.php';
        require 'order/order.php';
        require 'order/renewal.php';
        require 'auth/auth.php';
        require 'risk/risk.php';
        require 'repay/repay.php';
        require 'partner/partner.php';
        
        
        require 'col/columbia.php';

        require 'user/userinfo.php';
        require 'user/bankcard.php';
        require 'user/userinit.php';
        //order
        require 'order/contract.php';
        require 'callback/razorpay.php';
        require 'test/pay.php';
        //scoreone plugin
        require 'plugin/rupeecash.php';
        require 'coupon/coupon.php';
    });
});

$router->group([
    'prefix' => 'app',
], function (Router $router) {
    //callback
    require 'callback/digio.php';
    require 'callback/pay.php';
    require 'callback/callback.php';
    require 'callback/aadhaar.php';
    require 'callback/nbfc.php';
    require 'callback/risk.php';
    //贷超
    require 'loanmarket/fx.php';

    //对商户系统接口
    require 'saasMaster/merchant.php';
    require 'saasMaster/pull-consume-log.php';
});

$router->group([
    'prefix' => 'cloud',
], function (Router $router) {
    //callback
    require 'cloud/api.php';
});
