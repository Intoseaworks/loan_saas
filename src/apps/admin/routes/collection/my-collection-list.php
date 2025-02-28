<?php

use Laravel\Lumen\Routing\Router;

/**
 * 我的订单
 *
 * @var Router $router
 */

$router->group([
    'namespace' => 'Admin\Controllers\Collection',
    'path' => 'collection-management.mycollectionlist',
], function (Router $router) {
    //我的催收订单列表
    $router->get('collection/my_order_index', 'CollectionController@myOrderIndex');
    //我的催收订单详情
    $router->get('collection/my_order_view', 'CollectionController@myOrderView');
    //减免申请
    $router->post('collection/deduction-apply', 'CollectionController@deductionApply');
});
