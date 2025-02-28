<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Config;

use Admin\Models\Channel\Channel;
use Admin\Models\Collection\CollectionSetting;
use Admin\Models\Order\RepaymentPlan;
use Admin\Models\User\User;
use Admin\Models\User\UserInfo;
use Admin\Services\BaseService;
use Admin\Services\Collection\CollectionServer;
use Admin\Services\Collection\CollectionSettingServer;
use Admin\Services\NewApprove\NewApproveServer;
use Admin\Services\Notice\NoticeServer;
use Common\Models\Approve\Approve;
use Common\Models\BankCard\BankCardPeso;
use Common\Models\Collection\Collection;
use Common\Models\Collection\CollectionContact;
use Common\Models\Common\Config;
use Common\Models\Merchant\App;
use Common\Models\Notice\Notice;
use Common\Models\Order\Order;
use Common\Models\Risk\RiskBlacklist;
use Common\Models\Trade\AdminTradeAccount;
use Common\Models\Trade\TradeLog;
use Common\Models\User\UserAuth;
use Common\Models\User\UserBlack;
use Common\Models\User\UserWork;
use Common\Services\Partner\PartnerServer;
use Common\Utils\CityHelper;
use Common\Utils\Data\ArrayHelper;

class ConfigOptionServer extends BaseService {

    /**
     * 获取列表
     * @return mixed
     */
    public function getOption() {
        /** 用户相关 */
        $data = [];
        #用户类型
        $data['quality'] = ts(User::QUALITY, 'user');
        #学历
        $data['education_level'] = ArrayHelper::valToKeyVal(UserInfo::EDUCATIONAL_TYPE);
        #职业类型
        $data['employment_type'] = ArrayHelper::valToKeyVal(UserWork::EMPLOYMENT_TYPE);
        #客户端
        $data['client_id'] = User::CLIENT;
        #客户端版本号
        $data['app_version'] = ArrayHelper::valToKeyVal(['1.0.9', '1.0.8', '1.0.7', '1.0.6', '1.0.5', '1.0.4', '1.0.3', '1.0.2', '1.0.1', '1.0.0']);
        #订单App_client
        $data['app_client'] = ArrayHelper::valToKeyVal(['FX', 'android']);
        #包名下拉
        $data['page_name'] = App::model()->getAll('page_name', 'page_name');
        #用户来源
        $data['channel_name'] = Channel::model()->getAll('channel_name');
        #注册渠道
        $data['channel_code'] = Channel::model()->getAll('channel_code');
        #认证项
        $data['auth'] = ts(UserAuth::TYPE, 'auth');
        #黑名单类型
        $data['user_black_type'] = ts(UserBlack::TPYE, 'user');
        #推送发送方式
        $data['send_method'] = ts(NoticeServer::VALUE_SEND_METHOD, 'push');
        #推送状态
        $data['send_status'] = ts(NoticeServer::VALUE_SEND_STATUS, 'push');

        /** 审批相关 */
        #待审批状态
        $data['order_approval_pending_status'] = ts(array_only(Order::STATUS_ALIAS,
                        Order::APPROVAL_PENDING_STATUS), 'order');
        #审批拒绝状态
        $data['option_order_approval_reject_status'] = ts(array_only(Order::STATUS_ALIAS, Order::APPROVAL_REJECT_STATUS), 'order');
        #审批人
        $data['option_order_approver_list'] = NewApproveServer::server()->getApproverList();
        #审批流程
        $data['option_order_approval_process'] = ts(Approve::PROCESS, 'approve');

        /** 账户交易相关 */
        #账户业务类型
        $data['admin_account_type'] = ts(AdminTradeAccount::TYPE_ALIAS, 'pay');
        #账户支付方式
        $data['admin_account_payment_method'] = BankCardPeso::PAYMENT_TYPE;
        //为出款确认单独添加
        $data['admin_account_payment_method'] = array_merge($data['admin_account_payment_method'], BankCardPeso::PAYMENT_CHANNEL);
        #放款账户列表
        $data['admin_account_list'] = TradeLog::TRADE_PLATFORM;
        #交易结果
        $data['trade_log_result'] = ts(TradeLog::TRADE_RESULT, 'pay');
        #交易业务类型
        $data['trade_log_business_type'] = ts(TradeLog::BUSINESS_TYPE, 'pay');

        /** 订单相关 */
        #订单状态
        $data['order_status'] = ts(Order::STATUS_ALIAS, 'order');
        #放款订单状态
        $data['contract_status'] = ts(array_only(Order::STATUS_ALIAS, Order::REPAYMENT_PLAN_STATUS), 'order');
        #还款计划状态
        //$data['repayment_plan_status'] = ts(array_only(Order::STATUS_ALIAS, Order::REPAYMENT_PLAN_STATUS), 'order');
        $data['repayment_plan_status'] = ts(RepaymentPlan::STATUS_ALIAS, 'repayment');
        #人工还款订单状态
        $data['option_order_manual_repayment_status'] = ts(array_only(Order::STATUS_ALIAS, Order::WAIT_REPAYMENT_STATUS), 'order');

        /** 贷款相关 */
        $data['loan_days'] = ArrayHelper::valToKeyVal(range(1000, 100000, 1000));
        $data['loan_amount'] = ArrayHelper::valToKeyVal(range(1, 100, 1));

        /** 公告相关 */
        #公告标签
        $data['notice_tags'] = ts(Notice::TAGS, 'notice');
        #公告状态
        $data['notice_status'] = ts(Notice::STATUS, 'notice');

        /** 催收相关 */
        #催收状态
        $data['collection_status'] = ts(Collection::STATUS, 'collection');
        #我的催收状态
        $data['my_collection_status'] = ts(Collection::MY_COLLECTION_STATUS, 'collection');
        #催收等级
        $data['collection_level'] = [];
        #催收员
        $data['collector'] = CollectionServer::server()->getCollector();
        #减免设置
        $data['reduction_setting'] = ts(CollectionSetting::REDUCTION_SETTING);
        $data['collection_relation'] = ts(CollectionContact::RELATION, 'user');
        #催收等级
        $data['collection_level'] = ArrayHelper::valToKeyVal(CollectionSettingServer::server()->getLevel());
        $data['collection_status_val'] = \Common\Models\Collection\CollectionOnlineLog::STATUS_VALUE;
        $data['collection_target'] = \Common\Models\Collection\CollectionSetting::TARGET_TYPE;

        /** 商户相关 */
        #商户充值状态
        $data['partner_recharge_status'] = ts(PartnerServer::RECHARGE_STATUS, 'pay');

        #app列表
        $data['app_list'] = ts(App::model()->getAll('app_name'));

        #state 列表
//        $data['state_list'] = ArrayHelper::valToKeyVal(CityHelper::helper()->getStateList());

        $data['language'] = ArrayHelper::valToKeyVal(array_merge(['All'], Config::model()->getLanguage()));
        # 风控黑名单Keyword列表
        $data['risk_blacklist_keyword'] = RiskBlacklist::KEYWORD_ALIAS;
        $data['risk_blacklist_black_reason'] = ArrayHelper::valToKeyVal(RiskBlacklist::TYPE_ALIAS);

        $data['call_type'] = \Common\Models\Call\CallAdmin::TYPE;
        $data['call_status'] = \Common\Models\Call\CallAdmin::STATUS;
        $data['call_test_status'] = Collection::CALL_TEST_STATUS;
        $data['contact_method'] = \Common\Models\Collection\CollectionRecord::CONTACT_METHOD;

        $data['payout_channel'] = [
            "bank" => "Bank",
            "cash" => "Cash",
            "Coins" => "Coins",
            "Gcash" => "Gcash",
            "Paymaya" => "Paymaya",
        ];

        return ArrayHelper::arrsToOption($data);
    }

}
