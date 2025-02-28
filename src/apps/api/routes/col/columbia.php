<?php

use Laravel\Lumen\Routing\Router;

/**
 * 哥大项目
 * @var Router $router
 */
$router->group([
    'namespace' => 'Api\Controllers\Columbia',
    'prefix' => 'col',
        ],
        function (Router $router) {
    $router->get('index', 'ClockinController@index');
    $router->get('coupon-info', 'ClockinController@couponInfo');
    $router->post('punch', 'ClockinController@punch');
    
    $router->post('if-check-card', 'UserCheckController@checkStatus');
    $router->post('check-card', 'UserCheckController@verify');
});

