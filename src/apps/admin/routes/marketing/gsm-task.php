<?php

use Laravel\Lumen\Routing\Router;

/**
 * 借款订单
 *
 * @var Router $router
 */
$router->group([
    'namespace' => 'Admin\Controllers\Marketing',
    'path' => 'marketing.gsm',
        ], function (Router $router) {
            $router->get('marketing-gsm/sender-list', 'GsmTaskController@indexSender');
            $router->post('marketing-gsm/sender-save', 'GsmTaskController@saveSender');
            $router->get('marketing-gsm/sender-remove', 'GsmTaskController@removeSender');

            $router->get('marketing-gsm/tpl-list', 'GsmTaskController@indexTpl');
            $router->post('marketing-gsm/tpl-save', 'GsmTaskController@saveTpl');
            $router->get('marketing-gsm/tpl-remove', 'GsmTaskController@removeTpl');
            $router->post('marketing-gsm/tpl-import', 'GsmTaskController@importTpl');

            $router->get('marketing-gsm/task-list', 'GsmTaskController@indexTask');
            $router->post('marketing-gsm/task-save', 'GsmTaskController@saveTask');
            $router->get('marketing-gsm/task-remove', 'GsmTaskController@removeTask');
        });
