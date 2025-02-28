<?php

use Laravel\Lumen\Routing\Router;

/**
 * 借款订单
 *
 * @var Router $router
 */

$router->group([
    'namespace' => 'Admin\Controllers\Offline',
    'path' => 'offline.loan-export',
], function (Router $router) {
    $router->get('loan/export', 'LoanController@index');
    $router->get('loan/import', 'LoanController@import');
    $router->post('loan/confirm', 'LoanController@confirm');
    $router->post('loan/upload', 'LoanController@upload');
    $router->get('repayment/import', 'RepaymentController@import');

    $router->post('repayment/confirm', 'RepaymentController@confirm');
    $router->post('repayment/upload', 'RepaymentController@upload');
});
