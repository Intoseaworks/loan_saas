<?php

use Admin\Controllers\Partner\PartnerAccountController;
use Laravel\Lumen\Routing\Router;

/**
 * 商户信息设置
 *
 * @var Router $router
 */

$router->group([
    'path' => 'system-settings.merchant-info',
], function (Router $router) {
    // 商户详情
    $router->get('partner-account/partner/detail', PartnerAccountController::class . '@partnerDetail');
    // 更新商户
    $router->post('partner-account/partner/detail', PartnerAccountController::class . '@partnerDetail');
});
