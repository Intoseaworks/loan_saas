<?php

use Admin\Controllers\Notice\NoticeController;
use Laravel\Lumen\Routing\Router;

/**
 * 公告列表
 *
 * @var Router $router
 */

$router->group([
    'path' => 'inform-management.affichelist',
], function (Router $router) {
    //公告管理
    $router->get('notice/notice-list', NoticeController::class . '@noticeList');
    $router->get('notice/notice-template-list', NoticeController::class . '@noticeTemplateList');
    $router->get('notice/notice-list-all', NoticeController::class . '@noticeListAll');
    $router->post('notice/notice-create', NoticeController::class . '@noticeCreate');
    $router->post('notice/notice-template-create', NoticeController::class . '@noticeTemplateCreate');
    $router->post('notice/notice-edit', NoticeController::class . '@noticeEdit');
    $router->post('notice/notice-template-edit', NoticeController::class . '@noticeTemplateEdit');
    // 详情中删除
    $router->post('notice/notice-delete', NoticeController::class . '@noticeDelete');
    // 列表中删除（仅限已发送）
    $router->post('notice/notice-delete-by-send', NoticeController::class . '@noticeDeleteBySend');
    $router->get('notice/notice-detail', NoticeController::class . '@noticeDetail');
});
