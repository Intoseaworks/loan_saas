<?php

use Api\Controllers\Test\PayController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([],
    function (Router $router) {
        $router->post('test/htmlpay', PayController::class . '@htmlpay');
    }
);

