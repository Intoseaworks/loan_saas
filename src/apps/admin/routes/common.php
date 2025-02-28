<?php

use Admin\Controllers\Channel\ChannelController;
use Admin\Controllers\Collection\CollectionConfigController;
use Admin\Controllers\Collection\CollectionContactController;
use Admin\Controllers\Collection\CollectionController;
use Admin\Controllers\Collection\CollectionDeductionController;
use Admin\Controllers\Collection\CollectionRecordController;
use Admin\Controllers\Common\MessageController;
use Admin\Controllers\Config\ConfigController;
use Admin\Controllers\Login\LoginController;
use Admin\Controllers\Staff\StaffController;
use Admin\Controllers\Upload\UploadController;
use Common\Services\Rbac\Controllers\MenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Laravel\Lumen\Routing\Router;
use Admin\Controllers\User\UserContactController;

/**
 * 公共权限,不走rbac
 *
 * @var Router $router
 */

$router->group([],
    function (Router $router) {

        $router->get('admin/user', LoginController::class . '@info');
        $router->get('login/logout', LoginController::class . '@logout');

        //根据角色获取菜单列表
        $router->get('rbac/menu/menu-list', MenuController::class . '@menuList');
        //菜单列表
        $router->get('rbac/menu/index', MenuController::class . '@index');

        // 修改个人密码
        $router->post('staff/edit-password', StaffController::class . '@editPassword');
        $router->post('staff/check-password', StaffController::class . '@checkPassword');

        //上传
        $router->post('upload', UploadController::class . '@create');
        $router->options('upload', function () {
            header('HTTP/1.1 204 No Content');
        });

        //下载
        $router->get('downloadByType', UploadController::class . '@downloadByType');

        //下拉选项配置

        $router->get('config/view', ConfigController::class . '@view');
        //催收设置需要登录后获取到商户,所以放到这了
        $router->get('config-option/info', ConfigController::class . '@option');

        //联系结果催收进度下拉
        $router->get('collection_config/option_dial_progress', CollectionConfigController::class . '@optionDialProgress');
        //联系结果下拉
        $router->get('collection_config/option_dial', CollectionConfigController::class . '@optionDial');
        //催收进度下拉
        $router->get('collection_config/option_progress', CollectionConfigController::class . '@optionProgress');

        // TODO 催收订单和我的订单都会用这些接口,暂时先放在这里.
        // 获取系统贷款天数
        $router->get('loan-config/get-loandays-setting', \Admin\Controllers\Config\LoanMultipleConfigController::class . '@getLoandaysSetting');
        // 获取系统银行列表
        $router->get('config/bank-dicts', ConfigController::class . '@bankDicts');
        // 获取系统字典列表
        $router->get('config/dict', ConfigController::class . '@dict');
        //催收设置列表
        $router->get('collection_setting/index', \Admin\Controllers\Collection\CollectionSettingController::class.'@index');
        //减免历史
        $router->post('collection/deduction-history', CollectionController::class . '@deductionHistory');
        // 特定管理员人员列表,如催收管理员
        $router->get('staff/staff-index-special', StaffController::class . '@staffIndexSpecial');
        //添加催收联系人
        $router->post('collection_contact/create', CollectionContactController::class . '@create');
        //催收联系人短信
        $router->post('collection_contact/create_sms', CollectionContactController::class . '@createSms');
        //减免信息
        $router->get('collection_deduction/info', CollectionDeductionController::class . '@info');
        //添加减免
        $router->post('collection_deduction/create', CollectionDeductionController::class . '@create');
        //添加催记
        $router->post('collection_record/create', CollectionRecordController::class . '@create');
        // 一笔单部分还款开关设置
        $router->post('collection/set-order-part-repay-on', CollectionController::class . '@setOrderPartRepayOn');

        //渠道每日投放效果实时统计
        $router->get('channel/monitor-item', ChannelController::class . '@monitorItem');

        //获取所有消息通知
        $router->get('message/all', MessageController::class . '@getAll');
        //标记消息已读
        $router->post('message/read', MessageController::class . '@read');

        $router->post('user/create-contact', UserContactController::class . '@createUserContact');
        //催收员上线
        $router->post('collection/online', CollectionController::class . '@online');
        //催收员下线
        $router->post('collection/offline', CollectionController::class . '@offline');
        //催收员状态
        $router->post('collection/status', CollectionController::class . '@status');
        //催收员状态
        $router->post('collection/warning', CollectionController::class . '@warning');
        $router->post('collection/sendmail', CollectionController::class . '@sendmail');
        $router->post('collection/today-finish', CollectionController::class . '@todayFinish');

        //广播权限认证
        $router->post('broadcasting/auth', function (Request $request) {
            return Broadcast::auth($request);
        });
        
        //承诺还款时间段
        $router->get('collection_config/option_time_slot', CollectionConfigController::class . '@optionTimeSlot');
        
        $router->get('collection_record/back_out', CollectionRecordController::class . '@backOut');
    });

