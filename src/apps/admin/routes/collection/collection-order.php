<?php

use Admin\Controllers\Collection\CollectionAssignController;
use Admin\Controllers\Collection\CollectionController;
use Laravel\Lumen\Routing\Router;

/**
 * 催收订单
 *
 * @var Router $router
 */
$router->group([
    'path' => 'collection-management.collectionorder',
], function (Router $router) {
    //催收订单列表
    $router->get('collection/order_index', CollectionController::class . '@orderIndex');
    //催收订单详情
    $router->get('collection/order_view', CollectionController::class . '@orderView');
    //催收人工派单
    $router->post('collection/assign-to-collector', CollectionAssignController::class . '@assignToCollector');
    //催收排除名单
    $router->get('collection/blacklist', CollectionController::class . '@collectionBlackList');
    //催收加入/移除黑名单
    $router->get('collection/switch-blacklist', CollectionController::class . '@switchBlackList');
    //催收展期试算
    $router->get('collection/renewal_calc', CollectionController::class . '@renewalCalc');
    //催收黑名单导入
    $router->post('collection/import_blacklist', CollectionController::class . '@importBlackList');

    //减免申请列表
    $router->post('collection/deduction-wait-approve', CollectionController::class . '@deductionWaitApprove');
    //减免历史
//    $router->post('collection/deduction-history', CollectionController::class . '@deductionHistory');
    //减免审批
    $router->post('collection/deduction-approve', CollectionController::class . '@deductionApprove');
    
});
