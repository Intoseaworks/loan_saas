<?php

use Laravel\Lumen\Routing\Router;

/**
 * 放款订单
 *
 * @var Router $router
 */

$router->group([
    'namespace' => 'Admin\Controllers\Order',
    'path' => 'borrowing.loan',
], function (Router $router) {
    $router->get('contract/index', 'ContractController@index');
    $router->get('contract/view', 'ContractController@view');
    $router->get('contract/download', 'ContractController@download');
});
