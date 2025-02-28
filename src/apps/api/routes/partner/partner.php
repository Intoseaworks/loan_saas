<?php

use Api\Controllers\Partner\PartnerController;
use Laravel\Lumen\Routing\Router;

/**
 * 商户
 * @var Router $router
 */
$router->group([], function (Router $router) {
    $router->get('partner/detection-excess', PartnerController::class . '@detectionExcess');
});

