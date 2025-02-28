<?php

use Api\Controllers\Callback\AirudderController;
use Api\Controllers\Callback\AppsFlyerController;
use Api\Controllers\Callback\WhatsappController;
use Api\Controllers\Callback\YxyController;
use Api\Controllers\Common\CallbackController;
use Api\Controllers\Common\FawryCallbackController;
use Api\Controllers\Common\PaymobCallbackController;
use Laravel\Lumen\Routing\Router;

/**
 * 公共回调
 * @var Router $router
 */

$router->group([
    'prefix' => 'callback',
],
    function (Router $router) {
        //代付回调
        $router->post('daifu-notice', CallbackController::class . '@daifuNotice');
        //代扣回调
        $router->post('daikou-notice', CallbackController::class . '@daikouNotice');
        //what'app webhook
        $router->post('whatsapp', WhatsappController::class . '@webhook');
        $router->get('whatsapp', WhatsappController::class . '@webhook');
        $router->head('whatsapp', WhatsappController::class . '@webhook');
        //appsflyer-u5
        $router->post('appsflyer', AppsFlyerController::class . '@webhook');
        $router->get('appsflyer', AppsFlyerController::class . '@webhook');
        
        //appsflyer
        $router->post('appsflyer2', AppsFlyerController::class . '@webhook2');
        $router->get('appsflyer2', AppsFlyerController::class . '@webhook2');
        
        //Airudder webhook
        $router->post('airudder', AirudderController::class . '@webhook');
        $router->get('airudder', AirudderController::class . '@webhook');
        $router->head('airudder', AirudderController::class . '@webhook');


        //Fawry 还款 prod http://saas.cashcategp.com/app/callback/fawry http://120.78.230.66:8086/callback/fawry
        $router->post('fawry', FawryCallbackController::class . '@repay');
        $router->post('paymob-payout', PaymobCallbackController::class . '@payout');
        $router->get('paymob-payout', PaymobCallbackController::class . '@payout');
        
        //skyPay 还款成功通知 CollectionCollect
        $router->post('yxy-ai', YxyController::class . '@webhook');
        //globe短信回调
        $router->post('globe-surity', CallbackController::class . '@globeSurityCashRedirect');
        $router->get('globe-surity', CallbackController::class . '@globeSurityCashRedirect');
    });

