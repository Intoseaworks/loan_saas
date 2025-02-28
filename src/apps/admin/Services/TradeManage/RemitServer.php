<?php

namespace Admin\Services\TradeManage;

use Admin\Models\BankCard\BankCard;
use Admin\Models\Order\Order;
use Admin\Models\Trade\TradeLog;
use Admin\Services\BaseService;
use Admin\Services\Repayment\RepaymentPlanServer;
use Api\Models\Order\OrderDetail;
use Common\Events\Order\OrderFlowPushEvent;
use Common\Events\Order\OrderRemitSuccessEvent;
use Common\Events\Risk\RiskDataSendEvent;
use Common\Models\BankCard\BankCardPeso;
use Common\Models\Common\Config;
use Common\Models\Trade\AdminTradeAccount;
use Common\Redis\Remit\RemitRedis;
use Common\Services\Order\OrderServer;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\LoginHelper;
use Illuminate\Support\Facades\DB;
use Common\Jobs\Risk\MaxLevelUpdateJob;

class RemitServer extends BaseService
{
    /**
     * 判断订单是否已进入人工放款 / 判断订单是否已进入系统放款(系统放款)
     * 未进入或放款人为当前管理员则设置 lock
     * @param $orderId
     * @param $isSystem
     * @return bool
     */
    public function lockManualRemit($orderId, bool $isSystem = false)
    {
        $currentAdminId = $isSystem ? '-1' : LoginHelper::getAdminId();
        $adminId = RemitRedis::redis()->getByOrderId($orderId);

        if ($isSystem && isset($adminId)) {
            return false;
        }

        if (isset($adminId) && $adminId != $currentAdminId) {
            return false;
        }

        return RemitRedis::redis()->set($orderId, $currentAdminId);
    }

    /**
     * 判断订单放款人是否当前管理员
     * @param $orderId
     * @return bool
     */
    public function lockIsCurrentAdmin($orderId)
    {
        $adminId = LoginHelper::getAdminId();
        return $adminId == RemitRedis::redis()->getByOrderId($orderId);
    }

    public function getNextOrder()
    {
        $exceptOrderIds = RemitRedis::redis()->getAllLockOrderId();

        $order = Order::model()->whereNotIn('id', $exceptOrderIds)
            ->whereIn('status', Order::WAIT_PAY_STATUS)
            ->first();
        return $order;
    }

    /**
     * 人工出款列表
     * @param $params
     * @return array|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list($params)
    {
//        if (BasePayServer::server()->hasDaifuOpen()) {
//            return [];
//        }

        //查找正在人工放款的所有订单
        $exceptOrders = RemitRedis::redis()->getByOrderId();
        $exceptOrderIds = array_keys(array_diff($exceptOrders, [LoginHelper::getAdminId()]));

//        $query = Order::model()->search($params, ['manualApprove']);
        $query = Order::model()->searchRemit($params, ['manualApprove']);

        #人工审批时间
        if ($lastApproveTime = array_get($params, 'manual_approve_time')) {
            if (count($lastApproveTime) == 2) {
                $lastApproveTimeStart = current($lastApproveTime);
                $lastApproveTimeEnd = last($lastApproveTime);
                $query->whereBetween('pass_time', [$lastApproveTimeStart, $lastApproveTimeEnd]);
            }
        }
        //排除被上了锁的订单
        if ($exceptOrderIds) {
            $query->whereNotIn('id', $exceptOrderIds);
        }

        $perPage = array_get($params, "per_page", 20);
        $datas = $query->paginate($perPage == 20 ? 50 : $perPage);
        //@phan-suppress-next-line PhanTypeNoPropertiesForeach
        foreach ($datas as $data) {
            /** @var $data Order */
            $data->setScenario(Order::SCENARIO_LIST)->getText();
            $data->user && $data->user->getText(['telephone', 'fullname', 'quality']);
            $bankCard = $data->user->bankCards
                ->where('account_no', $data->bank_card_no)
                ->where('status', BankCard::STATUS_ACTIVE)
                ->first();
            if ($bankCard) {
                $bankCard->setScenario(BankCard::SCENARIO_DETAIL)->getText();
            }
            $data->bankCard = optional($bankCard)->toArray();

            $data->manual_approve_time = (string)$data->pass_time;
            unset($data->lastRepaymentPlan);
        }
        return $datas;
    }

    /**
     * 人工确认放款(放款支付渠道)
     * @param Order $order
     * @param $platform
     * @throws \Exception
     */
    public function manualConfirmLoan(Order $order, $platform)
    {
        $user = $order->user;
//        OrderCheckServer::server()->canSignOrder($order, $user);
        /** 从银行卡获取取款方式 */
        $paymentType = $user->bankCard->payment_type ?? "";
        if($paymentType != BankCardPeso::PAYMENT_TYPE_BANK){
            //return true;
        }

        # 不在贷放款状态
        if(!in_array($order->status, Order::WAIT_SIGN)){
            return true;
        }
        DB::beginTransaction();
        /** 绑定银行卡 */
        if (!OrderServer::server()->toSign($user->order->id, ['pay_channel' => $platform, 'pay_type' => $paymentType])) {
            DB::rollBack();
            return $this->outputException('确认放款失败');
        }else{
            //\Common\Services\NewClm\ClmServer::server()->updateMaxLevel($user);
            dispatch(new MaxLevelUpdateJob($user->id));
        }
        # 这里写更新 max_level
        DB::commit();
        $order->refresh();
        if ($order->getPaymentType() != BankCardPeso::PAYMENT_TYPE_CASH) {
            event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_APPROVE_PASS));
        }
        return true;
    }

    /**
     * 人工出款（废弃）
     * @param Order $order
     * @param $adminTradeAccountNo
     * @param $tradeResult
     * @param string $remark
     * @return TradeLog
     * @throws \Exception
     */
    public function manualRemitSubmit(Order $order, $adminTradeAccountNo, $tradeResult, $remark = '')
    {
        $tradeAccountNo = OrderDetail::model()->getBankCardNo($order);
        $bankCard = BankCard::getByUserIdAndNo($order->user_id, $tradeAccountNo);

        // 根据卡号动态创建后台手动支付账号
        $adminTradeAccount = AdminTradeAccount::createManualAccount($adminTradeAccountNo);

//        if (!$tradeAccountNo) {
//            return false;
//        }
        $telephone = $bankCard->reserved_telephone ? $bankCard->reserved_telephone : $order->user->telephone;
        $params = [
            'merchant_id' => $order->merchant_id,
            'trade_platform' => AdminTradeAccount::PAYMENT_METHOD_RELATE_PLATFORM[$adminTradeAccount->payment_method],
            'user_id' => $order->user_id,
            'admin_trade_account_id' => $adminTradeAccount->id,
            'bank_card_id' => $bankCard->id,
            'master_related_id' => $order->id,
            'master_business_no' => $order->order_no,
            'business_type' => TradeLog::BUSINESS_TYPE_MANUAL_REMIT,
            'trade_type' => TradeLog::TRADE_TYPE_REMIT,
            'request_type' => TradeLog::REQUEST_TYPE_ADMIN,
            'business_amount' => $order->getPaidAmount(false),
            'trade_amount' => $order->getPaidAmount(false),
            'bank_name' => $bankCard->bank_name,
            'trade_account_telephone' => $telephone,
            'trade_account_name' => $bankCard->name ?? '',
            'trade_account_no' => $tradeAccountNo ?? '',
            'trade_desc' => $remark,
            //'trade_result_time' => $params['trade_result_time'],
            //'trade_notice_time' => $params['trade_notice_time'],
            //'trade_settlement_time' => $params['trade_settlement_time'],
            //'trade_request_time' => $params['trade_request_time'],
            'admin_id' => LoginHelper::getAdminId(),
            'handle_name' => LoginHelper::$modelUser['username'] ?? '',
        ];

        $tradeLog = TradeLog::model()->add($params);
        $tradeLog->evolveStatusTrading();

        $dateTime = $this->getDateTime();
        if ($tradeResult == TradeLog::TRADE_RESULT_SUCCESS) {
            //流转放款成功
            $this->flowRemitSuccess($tradeLog, $tradeLog->trade_amount, $dateTime);
        } else {
            //流转放款失败
            $this->flowRemitFailed($tradeLog, $dateTime);
        }

        return $tradeLog;
    }

    /**
     * 流转放款成功
     * @param TradeLog|\Common\Models\Trade\TradeLog $tradeLog
     * @param $amount
     * @param $resultTime
     * @param bool $isSystem
     * @param $params
     * @throws \Exception
     */
    public function flowRemitSuccess($tradeLog, $amount, $resultTime, $isSystem = false, $params = [])
    {
        DB::beginTransaction();
        try {
            /** @var Order $order */
            $order = $tradeLog->order;
            //交易记录状态更新
            $tradeLog->evolveStatusOverResultSuccess($params['tradeNo'] ?? '', $resultTime, $resultTime, $params['tradeAmount'] ?? null);
            //流转订单状态&记录放款时间、渠道、金额
            $params = [
                'paid_time' => date("Y-m-d 00:00:00", strtotime($resultTime)),
                'paid_amount' => $amount,
                'pay_channel' => $tradeLog->trade_platform,
            ];
            if ($isSystem) {
                OrderServer::server()->systemPaid($order, $params);
            } else {
                OrderServer::server()->manualPaid($order, $params);
            }
            /*飞象回调*/
//            FeixiangServer::server()->payoutNotify($order, $amount);
            //添加还款计划
            RepaymentPlanServer::server()->create($order);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            EmailHelper::send("系统出款：{$isSystem} \n error:{$e->getMessage()} \n trade_log_id:{$tradeLog->id}", '出款成功流转报错');
            throw $e;
        }
        //推送
        event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_PAY_SUCCESS));
        //出款成功事件触发：检查余额预警...
        event(new OrderRemitSuccessEvent($order->id, $order->merchant_id));
        //NBFC上报节点触发
//        event(new NbfcReportEvent($order->id, NbfcReportConfig::REPORT_NODE_REMIT));
        //风控数据上报
        event(new RiskDataSendEvent($order->user_id, RiskDataSendEvent::NODE_ORDER_PAID));
    }

    /**
     * 流转放款失败
     * @param TradeLog|\Common\Models\Trade\TradeLog $tradeLog
     * @param $resultTime
     * @param $isSystem
     * @param $params
     * @throws \Exception
     */
    public function flowRemitFailed($tradeLog, $resultTime, $isSystem = false, $params = [])
    {
        DB::beginTransaction();
        try {
            $order = $tradeLog->order;
            //交易记录状态更新
            $tradeLog->evolveStatusOverResultFailed(
                $params['requestNo'] ?? '',
                $params['tradeNo'] ?? '',
                $resultTime,
                $params['msg'] ?? '',
                $params['tradeResultCode'] ?? ''
            );
            //流转订单状态
            if ($isSystem) {
                OrderServer::server()->systemPayFail($order->id);

                // 根据失败原因判断是否需要流转回待放款状态
                if ($tradeLog->refresh()->isNeedAfreshTrade()) {
                    OrderServer::server()->payFailAfreshPay($order->id);
                }
                // 余额不足关闭自动放款开关
                if ($tradeLog->isInsufficientBalance()) {
                    Config::createOrUpdate(Config::KEY_SYS_AUTO_REMIT, 0, $tradeLog->merchant_id);

                    DingHelper::notice("orderId：{$tradeLog->master_related_id}", "自动放款关闭:merchantId:{$tradeLog->merchant_id}", DingHelper::AT_SOLIANG);
                }
            } else {
                OrderServer::server()->manualPayFail($order->id);
            }

            /*飞象回调*/
            //FeixiangServer::server()->payoutNotify($order, $order->paid_amount, false);
            //清理用户银行卡信息
            //UserAuthServer::server()->clearBankCard($order->user_id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            EmailHelper::send("系统出款：{$isSystem} \n error:{$e->getMessage()} \n trade_log_id:{$tradeLog->id}", '出款失败流转报错');
            throw $e;
        }
        //推送
        //event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_PAY_FAIL));
    }
}
