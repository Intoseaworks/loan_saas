<?php

use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */

$router->group([
    'namespace' => 'Admin\Controllers\Test',
], function (Router $router) {
    //催收分单
    $router->get('test/console/collection_assign', 'TestConsoleController@collectionAssign');
    //流转坏账
    $router->get('test/console/flow_collection_bad', 'TestConsoleController@flowCollectionBad');
});
