<?php

use Admin\Controllers\Crm\MarketingController;
use Admin\Controllers\Crm\SmsTemplateController;
use Admin\Controllers\Crm\CollectionSmsTemplateController;
use Laravel\Lumen\Routing\Router;

/**
 *
 * @var Router $router
 */
$router->group([
    'path' => 'crm-marketing',
        ], function (Router $router) {
    //营销上传
    $router->post('crm/marketing/upload', MarketingController::class . '@upload');
    //营销批次状态变更
    $router->post('crm/marketing/batch-set-status', MarketingController::class . '@batchSetStatus');
    //营销状态变更
    $router->post('crm/marketing/set-status', MarketingController::class . '@setStatus');
    //营销延期
    $router->post('crm/marketing/postpone', MarketingController::class . '@postpone');
    //营销批次延期
    $router->post('crm/marketing/postpone-betch', MarketingController::class . '@postponeBetch');
    //营销批次列表
    $router->post('crm/marketing/index-batch', MarketingController::class . '@indexBatch');
    //营销列表
    $router->post('crm/marketing/index', MarketingController::class . '@index');
    //保存SMS营销任务前客户数试算
    $router->post('crm/marketing/task-precount-customers', MarketingController::class . '@calConditionCustomers');
    //保存SMS营销任务
    $router->post('crm/marketing/task-sms', MarketingController::class . '@taskSms');
    //保存PHONE营销任务
    $router->post('crm/marketing/task-telephone', MarketingController::class . '@taskTelephone');
    //任务列表
    $router->post('crm/marketing/task-index', MarketingController::class . '@taskIndex');
    //任务列表导出
    $router->post('crm/marketing/report', MarketingController::class . '@report');
    //任务详情
    $router->post('crm/marketing/task-records', MarketingController::class . '@taskRecords');

    //获取短信模板
    $router->post('crm/sms/template', SmsTemplateController::class . '@getOne');
    //短信模板保存
    $router->post('crm/sms/template-save', SmsTemplateController::class . '@smsTemplateSave');
    //停用短信模板
    $router->post('crm/sms/template-status', SmsTemplateController::class . '@smsTemplateSave');
    //短信模板列表
    $router->post('crm/sms/template-list', SmsTemplateController::class . '@smsTemplateIndex');

    //获取催收短信模板
    $router->post('crm/sms/collection-template', CollectionSmsTemplateController::class . '@getOne');
    //催收短信模板保存
    $router->post('crm/sms/collection-template-save', CollectionSmsTemplateController::class . '@smsTemplateSave');
    //停用催收短信模板
    $router->post('crm/sms/collection-template-status', CollectionSmsTemplateController::class . '@smsTemplateSave');
    //催收短信模板列表
    $router->post('crm/sms/collection-template-list', CollectionSmsTemplateController::class . '@smsTemplateIndex');
    //催收短信模板上传
    $router->post('crm/sms/collection-template-upload', CollectionSmsTemplateController::class . '@upload');

    //字典
    $router->post('crm/marketing/dict', MarketingController::class . '@dict');
    $router->post('crm/marketing/task-status', MarketingController::class . '@taskStatus');
});
