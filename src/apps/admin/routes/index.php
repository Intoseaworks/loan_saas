<?php

use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([
    'prefix' => 'api',
], function (Router $router) {
    //免登录访问接口
    require 'common/config.php';
    //登录接口
    require 'login/login.php';

    //供其他项目调用接口
    require 'api/data-statistics.php';


    $router->post('admin/login_test', \Admin\Controllers\Login\LoginController::class . '@loginTest');

    $router->group([
        'middleware' => ['setGuard:admin', 'auth:admin', 'setMerchant:api'],
    ], function (Router $router) {

        // 公共接口
        require 'common.php';

        $router->group([
            'middleware' => ['RBAC'],
            'path'       => 'admin',
        ], function (Router $router) {

            // 黑名单用户
            require 'user/blacklist.php';
            // 用户反馈
            require 'user/feedback-list.php';
            // 用户信息
            require 'user/user-list.php';
            // 测试用户
            require 'user/test-account.php';
            require 'columbia/clockin.php';
            // 用户
            require 'user/user.php';

            // 借款订单
            require 'order/borrow.php';
            // 放款订单
            require 'order/loan.php';
            // 放款订单
            require 'offline/loan.php';

            // 人员列表
            require 'auth/staff.php';
            // 角色列表
            require 'auth/role.php';
            // 菜单管理
            require 'auth/menus.php';

            // 安全设置
            require 'config/security-settings.php';
            // 货款设置
            require 'config/loan-settings.php';
            // 运营商设置
            require 'config/operate-settings.php';
            // 审批设置
            require 'config/approval-settings.php';
            // 催收设置
            require 'config/collection-settings.php';
            // 商户信息设置
            require 'config/partner-account.php';
            // 商户认证设置
            require 'config/auth-settings.php';
            // 商户还款设置
            require 'config/repay-settings.php';

            // 平台列表
            require 'channel/platform.php';
            // 流量监控
            require 'channel/traffic-monitoring.php';

            // 待审批订单
            require 'approve/await-list.php';
            // 人工审批
            require 'approve/artificial-list.php';
            // 审批拒绝订单
            require 'approve/refuse-list.php';

            // 支付记录
            require 'trade-manage/payment-record.php';
            // 人工出款
            require 'trade-manage/artificial-payment.php';
            // 出款失败处理
            require 'trade-manage/failure-payment.php';
            // 系统放款记录
            require 'trade-manage/system-loan-record.php';

            // 公告列表
            require 'notice/affiche-list.php';
            // 推送列表
            require 'notice/push-list.php';
            // 短信列表
            require 'notice/note-list.php';

            // 催收订单
            require 'collection/collection-order.php';
            // 我的订单
            require 'collection/my-collection-list.php';
            require 'collection/collector.php';

            // 催收率统计
            require 'collection-statistics/reclaimate-rate.php';
            // 催收订单统计
            require 'collection-statistics/urgeregain-order.php';
            // 催收员每日统计
            require 'collection-statistics/urgeregain-workers.php';
            require 'collection-statistics/urgeregain-efficiency.php';

            // 还款计划
            require 'repayment/repayment-list.php';
            // 人工还款
            require 'repayment/artificial-list.php';
            // 代扣还款记录
            require 'repayment/system-repay-list.php';

            // 已坏账订单
            require 'post-loan-management/bad-debet-list.php';
            // 已逾期订单
            require 'post-loan-management/overdue-list.php';
            // 已还款订单
            require 'post-loan-management/post-list.php';

            // 控制台
            require 'data-statistics/workbench.php';

            //每日流量效果
            require 'data-statistics/summary.php';

            // 每日收支分析
            require 'operate-data/balance-of-payments.php';
            // 每日贷后分析
            require 'operate-data/post-loan-analysis.php';
            // 渠道分析
            require 'channel-statistics/channel-statistics.php';

            //测试
            //if (\Yunhan\Utils\Env::isDevOrTest()) {
            $router->group([
                'middleware' => \Yunhan\Utils\Env::isDevOrTest() ? [] : ['TEST'],
                //'middleware' => ['TEST'],
            ], function (Router $router) {
                require 'test/auth.php';
                //订单
                require 'test/order.php';
                //定时任务
                require 'test/console.php';
            });
            //}

            //充值管理
            require 'partner/partner-recharge.php';
            //账户管理
            require 'partner/partner-account.php';

            //余额查询
            require 'partner/partner-balance.php';

            // 导出
            require 'export/export.php';

            //续期
            require 'repayment/repayment-renewal.php';
            //调账
            require 'repayment/repayment-repay.php';
            //crm
            require 'crm/whitelist.php';
            require 'crm/customer.php';
            require 'crm/marketing.php';
            require 'crm/telemarketing.php';
            //风控黑名单
            require 'risk/risk-blacklist.php';

            // clm
            require 'new-clm/clm.php';
            //coupon
            require 'coupon/coupon.php';
            require 'coupon/coupon-task.php';
            require 'coupon/coupon-receive.php';
            //activity
            require 'activity/activity.php';
            require 'activity/activity-award.php';
            require 'activity/activity-statistics.php';
            //email sender
            require 'email/setting.php';
            //marketing
            require 'marketing/gsm-task.php';
        });
    });
});
