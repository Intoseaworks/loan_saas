<?php

namespace Common\Listeners\Order;

use Common\Events\Order\OrderFlowPushEvent;
use Common\Jobs\Push\App\AppByCommonJob;
use Common\Jobs\Push\App\AppByPayScheduleJob;
use Common\Jobs\Push\Sms\SmsByCommonJob;
use Common\Models\BankCard\BankCardPeso;
use Common\Models\Merchant\App;
use Common\Models\Order\Order;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;
use Common\Utils\Data\DateHelper;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Push\Push;
use Common\Utils\Sms\SmsHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Common\Models\Trade\TradeLog;

class OrderFlowPushListener implements ShouldQueue
{
    public $queue = 'order-flow-push';

    /**
     * @var Order
     */
    protected $order;

    protected $userId;

    protected $highlightColor = '#FF8C00';

    protected $appName;
    protected $url;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function handle(OrderFlowPushEvent $event)
    {
        try {
            MerchantHelper::clearMerchantId();
            if (!$order = Order::query()->where('id', $event->getOrderId())->first()) {
                throw new \Exception('订单不存在');
            }
            MerchantHelper::setAppId($order->app_id, $order->merchant_id);
            $this->order = $order;
            $this->userId = $order->user_id ?? $event->getUserId();
            $type = $event->getType();
            $this->setAppData();
            $this->push($type);
            /*if(!in_array($type, [OrderFlowPushEvent::TYPE_INTO_APPROVE])){
                DingHelper::notice([
                    'user_id' => $this->userId ?? '',
                    'order' => $this->order ? $this->order->id : '',
                    'type' => $type ?? '',
                ], 'push记录');
            }*/
            
            
            return true;
        } catch (\Exception $e) {
            DingHelper::notice([
                'orderId' => $event->getOrderId(),
                'merchantId' => MerchantHelper::getMerchantId(),
                'appId' => MerchantHelper::getAppId(),
                'user_id' => $this->userId ?? '',
                'order' => $this->order ?? '',
                'type' => $type ?? '',
                'e' => EmailHelper::warpException($e),
            ], 'push异常');
            return false;
        }

    }

    public function setAppData()
    {
        /** @var App $app */
        $app = App::find($this->order->app_id);
        $this->appName = $app->app_name ?? '';
        $this->url = $app->app_url ?? '';
    }

    public function push($type)
    {
        switch ($type) {
            /*签约后置，无需在创建订单后营销
             * case OrderFlowPushEvent::TYPE_WAIT_LOAN:
                $this->pushWaitLoan();
                break;*/
            case OrderFlowPushEvent::TYPE_INTO_APPROVE:
                $this->pushIntoApprove();
                break;
            case OrderFlowPushEvent::TYPE_REPLENISH:
                $this->pushReplenish();
                break;
            case OrderFlowPushEvent::TYPE_APPROVE_TO_CALL:
                $this->pushApproveToCall();
                break;
            case OrderFlowPushEvent::TYPE_APPROVE_PASS:
                $this->pushApprovePass();
                break;
            case OrderFlowPushEvent::TYPE_PAY_FAIL:
                $this->pushPayFail();
                break;
            case OrderFlowPushEvent::TYPE_PAY_SUCCESS:
                $this->pushPaySuccess();
                break;
            /*case OrderFlowPushEvent::TYPE_OVERDUE:
                $this->pushOverdue();
                break;*/
            case OrderFlowPushEvent::TYPE_REPAY_FINISH:
                $this->pushRepayFinish();
                break;
            /*case OrderFlowPushEvent::TYPE_EXPIRATION_REMINDER:
                $this->pushExpirationReminder();
                break;*/
            case OrderFlowPushEvent::TYPE_DAIKOU_FAILED:
                $this->pushDaikouFailed();
                break;
            case OrderFlowPushEvent::TYPE_REPAY_REDUCTION:
                $this->pushRepayReduction();
                break;
            case OrderFlowPushEvent::TYPE_RENEWAL_SUCCESS:
                $this->renewalSuccess();
                break;
            case OrderFlowPushEvent::TYPE_DRAW_MONEY:
                $this->waitDrawMoney();
                break;
        }
    }

    /**
     * 待确认借款
     */
    protected function pushWaitLoan()
    {
        /*$user = User::find($this->userId);
        if (!$user) {
            return $this->sendErrorEmail('用户id查找用户为空：待确认借款');
        }
        $loanAmountMax = Config::model()->getLoanAmountMax($user->quality);

        $title = "还差一步完成借款";
        $content = "在借款首页完成确认借款，即可激活使用{$loanAmountMax}元额度";

        return Jpush::pushInbox($title, $content, $this->userId);*/
    }

    /**
     * 审批中
     */
    protected function pushIntoApprove()
    {
        $order = $this->order;

        $principal = $order->principal;

        $title = "{$principal} in line approval";
        $content = "Please wait patiently for {$principal} quota approval";

        return dispatch(new AppByCommonJob($order->user_id, $title, $content));
    }

    /**
     * 重新提交资料
     * @return array|bool|mixed|null
     */
    protected function pushReplenish()
    {
        $order = $this->order;
        if ($order->status != Order::STATUS_REPLENISH) {
            return $this->sendErrorEmail('订单状态不正确：重新提交资料');
        }

        $title = 'Re-upload the file to get the loans';
        $content = "Application re-approved, here's another chance to apply for instant loan, Log in now.";

        dispatch(new AppByCommonJob($order->user->id, $title, $content));
        dispatch(new SmsByCommonJob($order->user->id, SmsHelper::EVENT_APPROVE_REPLENISH, ['appName' => $this->appName, 'days' => 5]));
        return true;
    }

    /**
     * 人审通过电审中
     *
     * @return bool|null
     */
    protected function pushApproveToCall()
    {
        $order = $this->order;
        if (!in_array($order->status, [Order::STATUS_WAIT_CALL_APPROVE, Order::STATUS_WAIT_TWICE_CALL_APPROVE])) {
            return $this->sendErrorEmail('订单状态不正确：电审中');
        }

        $title = "Pay attention to verify call.";
        $content = "Dear Customer,we are processing your loan application.Please keep your phone accessible and pay attention to the {$this->appName} team phone.";
//        dispatch(new SmsByCommonJob($order->user->id, SmsHelper::EVENT_APPROVE_TO_CALL, ['appName' => $this->appName]));
        dispatch(new AppByCommonJob($order->user->id, $title, $content));
        return true;
    }

    /**
     * 审批通过
     * @return array|bool|mixed|null
     */
    protected function pushApprovePass()
    {
        $order = $this->order;
        if (!in_array($order->status, Order::WAIT_PAY_STATUS)) {
            return $this->sendErrorEmail('订单状态不正确：审批通过放款中');
        }

        $payAmount = $order->getPaidAmount(false);
        $title = "Last step to get the cash.";
        $content = "Don't miss your {$payAmount} approved loan amount. Please click to e-sign the for loan on {$this->appName} to initiate the amount transfer now.";

        // App推送
        dispatch((new AppByCommonJob($order->user_id, $title, $content)));
        // 短信推送
        dispatch((new SmsByCommonJob($order->user_id, SmsHelper::EVENT_ORDER_PASS_SMS, [
            'amount' => $payAmount, 'loan_days' => $order->loan_days, 'appName' => $this->appName
        ])));
        // IVR推送
//        dispatch((new IvrByCommonJob($order->user_id, SmsHelper::EVENT_ORDER_PASS_IVR, [
//            'amount' => $payAmount, 'appName' => $this->appName
//        ], PushCheckService::ORDER_IN_PASS, ['orderId' => $order->id]))->delay(60 * 60));

        return true;
    }

    /**
     * 放款失败
     * @return array|bool|mixed|null
     */
    protected function pushPayFail()
    {
        $order = $this->order;

        if (!in_array($order->status, [Order::STATUS_SYSTEM_PAY_FAIL, Order::STATUS_MANUAL_PAY_FAIL])) {
            return $this->sendErrorEmail('订单状态不正确：放款失败');
        }
        $tradeLog = $order->lastTradeLog;
        if (!$tradeLog) {
            return $this->sendErrorEmail('异常!订单交易记录不存在');
        }

        $title = "Contact us to re-apply a loan";
        $content = "Dear {$this->appName} users. we were failed to transfer money to your account. Please email us to cancel the loan.Update your bank account details and reapply the loan in our app.";

        // App推送
        dispatch(new AppByCommonJob($order->user_id, $title, $content));
        // 短信推送
        dispatch(new SmsByCommonJob($order->user_id, SmsHelper::EVENT_ORDER_PAY_FAIL, ['appName' => $this->appName]));

        return true;
    }

    /**
     * 待还款(出款成功)
     */
    protected function pushPaySuccess()
    {
        /** @var Order $order */
        $order = $this->order;

        if (!in_array($order->status, [Order::STATUS_MANUAL_PAID, Order::STATUS_SYSTEM_PAID])) {
            return $this->sendErrorEmail('订单状态不正确：待还款(出款成功)');
        }
        $tradeLog = $order->lastTradeLog;
        $repaymentPlan = $order->lastRepaymentPlan;
        if (!$tradeLog) {
            return $this->sendErrorEmail('异常!订单交易记录不存在:待还款(出款成功)');
        }
        if (!$repaymentPlan) {
            return $this->sendErrorEmail('异常!还款计划记录不存在:待还款(出款成功)');
        }

        $fullname = $order->user->fullname;
        $appointmentPaidDate = DateHelper::formatToDate($repaymentPlan->appointment_paid_time);
        $appointmentPaidTimeBeforeOneDay = DateHelper::subDays(1, 'Y-m-d H:i:s', $appointmentPaidDate);
        # 只有首贷推送
        if($order->quality == 0){
            \Common\Utils\Third\AppsflyerHelper::helper()->s2s("disbursement_success", ["disbursement_success" => "{$order->user->telephone}##{$order->id}", "order_id" => $order->id], $order->merchant_id, $order->user_id);
        }
        // App日程推送
        dispatch((new AppByPayScheduleJob(
            $order->user_id, $appointmentPaidTimeBeforeOneDay, $fullname, $appointmentPaidDate, '12'
        ))->delay(1));
        dispatch((new AppByPayScheduleJob(
            $order->user_id, $appointmentPaidDate, $fullname, $appointmentPaidDate, '08'
        ))->delay(60));
        dispatch((new AppByPayScheduleJob(
            $order->user_id, $appointmentPaidDate, $fullname, $appointmentPaidDate, '23'
        ))->delay(120));

        $title = 'Loan success';
        $content = "Dear {$this->appName} users.We have transferred the money to your account.Please check the account in time.Thank You.";
        $appName = App::find($order->app_id)->app_name ?? '';
        dispatch(new AppByCommonJob($order->user->id, $title, $content));

        dispatch(new SmsByCommonJob($order->user_id, SmsHelper::EVENT_PAY_SUCCESS_SMS_BANK, ['appName' => $appName, 'amount' => $order->getPaidAmount(true), 'reference_no' => $order->reference_no, 'dragonpay_lifetimeID' => $order->user->userInfo->dg_pay_lifetime_id]));
        return true;
    }

    protected function waitDrawMoney()
    {
        /** @var Order $order */
        $order = $this->order;
        $appName = App::find($order->app_id)->app_name ?? '';
        if ($order->status == Order::STATUS_PAYING) {
            $channel = $order->user->bankCard->instituion_name;
            $tradeLog = $order->tradeLogRemiting();
            if ($tradeLog) {
                switch ($tradeLog->trade_platform) {
                    case TradeLog::TRADE_PLATFORM_SKYPAY:
                        $channel = implode(',', TradeLog::WITHDRAWAL_INSTITUTION_SKYPAY);
                        break;
                    case TradeLog::TRADE_PLATFORM_DRAGONPAY:
                        $channel = implode(',', TradeLog::WITHDRAWAL_INSTITUTION_DRAGONPAY);
                        break;
                }
            }
            dispatch(new SmsByCommonJob($order->user_id, SmsHelper::EVENT_OFFLINE_DRAW, ['appName' => $appName, 'channel' => $channel, 'withdraw_no' => $order->withdraw_no]));
            return true;
        }
        return false;
    }

    /**
     * 已逾期
     */
    protected function pushOverdue()
    {
        /*$order = $this->order;

        if ($order->status != Order::STATUS_OVERDUE) {
            return $this->sendErrorEmail('订单状态不正确：已逾期');
        }
        $repaymentPlan = $order->lastRepaymentPlan;
        if (!$repaymentPlan) {
            return $this->sendErrorEmail('异常!还款计划记录不存在:已逾期');
        }
        $principal = $order->principal;
        $overdueFee = $order->overdueFee();
        //$reductionFee = $repaymentPlan->reduction_fee;
        $reductionFee = $order->getReductionFee();
        $repayAmount = $order->repayAmount();
        $appointmentPaidTime = $repaymentPlan->appointment_paid_time;
        $appointmentPaidTime = DateHelper::formatToDate($appointmentPaidTime);

        $title = "借款已逾期，请尽快处理还款";
        $content = "借款已逾期，请尽快处理还款：<br>借款金额：{$principal}元<br>逾期罚息：{$overdueFee}元<br>已减免：{$reductionFee}元<br>应还金额：{$repayAmount}元<br>应还日期：{$appointmentPaidTime}<br><br><span style=\"color:{$this->highlightColor};\">平台将按照逾期条款执行催还程序，为避免影响信用，请尽快处理还款！</span>";

        return Jpush::pushInbox($title, $content, $order->user_id);*/
    }

    /**
     * 已还款
     * @return array|bool|mixed|null
     */
    protected function pushRepayFinish()
    {
        $order = $this->order;

        $repaymentPlan = $order->lastRepaymentPlan;
        if (!$repaymentPlan) {
            return $this->sendErrorEmail('异常!还款计划记录不存在:已还款');
        }

        if (!in_array($order->status, [Order::STATUS_FINISH, Order::STATUS_OVERDUE_FINISH]) &&
            !$repaymentPlan->isFinish() &&
            !$repaymentPlan->isPartRepay()
        ) {
            return $this->sendErrorEmail('订单状态不正确：已还款');
        }

        //$principal = $order->principal;
        //$oughtRepayAmount = $order->repayAmount();
        $repayAmount = $repaymentPlan->repay_amount;
        $repayTime = $repaymentPlan->repay_time;
        //$repayTime = DateHelper::formatToDate($repayTime);
        //$repayTime = $repayTime == 0 ? '---' : $repayTime;
        //$overdueFee = $order->overdueFee();

        $title = "Successful repayment";

        $content = "Order {$order->order_no} repayment successful";

        $tip = "Successful repayment";

        // App推送
        dispatch(new AppByCommonJob($order->user_id, $title, $content, ['type' => 'paySuccess', 'tip' => $tip]));
    }

    /**
     * 到期还款提醒
     * 还款日前一天10点 & 还款日当天10点，且订单状态“待还款”、“还款失败”
     */
    protected function pushExpirationReminder()
    {
        /*$order = $this->order;

        if (!in_array($order->status, [Order::STATUS_MANUAL_PAID, Order::STATUS_SYSTEM_PAID])) {
            return $this->sendErrorEmail('订单状态不正确：到期还款提醒');
        }
        $repaymentPlan = $order->lastRepaymentPlan;
        if (!$repaymentPlan) {
            return $this->sendErrorEmail('异常!还款计划记录不存在:已逾期');
        }
        $oughtRepayAmount = $order->repayAmount();
        $appointmentPaidTime = $repaymentPlan->appointment_paid_time;
        $appointmentPaidTime = DateHelper::formatToDate($appointmentPaidTime);

        $title = "借款即将到期，请尽快处理还款";
        $content = "借款即将到期，请尽快处理还款：<br>应还金额：{$oughtRepayAmount}元<br>应还日期：{$appointmentPaidTime}";

        return Jpush::pushInbox($title, $content, $order->user_id);*/
    }

    /**
     * 扣款失败
     *
     */
    protected function pushDaikouFailed()
    {
        $order = $this->order;
        // 已经完结的订单，不在推送还款失败
        if (in_array($order->status, Order::FINISH_STATUS)) {
            return true;
        }
        if (!in_array($order->status, array_merge(Order::BE_OVERDUE_STATUS, [Order::STATUS_COLLECTION_BAD]))) {
            return $this->sendErrorEmail('订单状态不正确：还款失败');
        }
        $repaymentPlan = $order->lastRepaymentPlan;
        if (!$repaymentPlan) {
            return $this->sendErrorEmail('异常!还款计划记录不存在:还款失败');
        }

        $title = "Failure Repay";
        $content = "There was a failure in the repayment transation. Please try again.";
        $tip = "There was a failure in the repayment transation. Please try again.";
        // App推送
        dispatch(new AppByCommonJob($order->user_id, $title, $content, ['type' => 'payFail', 'tip' => $tip]));
//        dispatch(new SmsByCommonJob($order->user_id, SmsHelper::EVENT_PAY_FAIL_SMS));
//        dispatch(new IvrByCommonJob($order->user_id, SmsHelper::EVENT_PAY_FAIL_IVR));
    }

    /**
     * 自动减免成功
     *
     * @return bool|mixed
     */
    public function pushRepayReduction()
    {
        $title = "Deduction successfully";
        $content = "Your credit is good, the deduction has been successful, and the loan has been settled.";
        return Push::helper()->pushInbox($title, $content, $this->userId, [], true);
    }

    /**
     * 展期成功
     * @return bool
     */
    private function renewalSuccess()
    {
        $order = $this->order;
        $subject = CalcRepaymentSubjectServer::server($order->lastRepaymentPlan)->getSubject();
        dispatch(new SmsByCommonJob($order->user_id, SmsHelper::EVENT_RENEWAL, ['amount' => $subject->renewalPaidAmount]));
        return true;
    }

    /**
     * 邮件报错
     * @param string $msg
     * @return null
     */
    protected function sendErrorEmail($msg = '')
    {
        $backtrace = debug_backtrace();
        $functionName = optional($backtrace[1])['function'];
        $time = date('Y-m-d H:i:s');
        DingHelper::notice($functionName . "调用错误\norderId:{$this->order->id}\ncurrentStatus:{$this->order->status}\ntime:{$time}\n" . $msg, '订单状态流转app推送状态错误');
        return null;
    }
}
