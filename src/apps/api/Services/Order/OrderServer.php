<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\Order;

use Admin\Services\User\UserServer;
use Api\Models\Order\Order;
use Api\Models\Order\RepaymentPlan;
use Api\Models\User\User;
use Api\Services\Auth\AuthServer;
use Carbon\Carbon;
use Common\Console\Services\Risk\SystemApproveServer;
use Common\Events\Order\OrderAgreementEvent;
use Common\Events\Order\OrderCreateEvent;
use Common\Events\Order\OrderFlowPushEvent;
use Common\Events\Risk\RiskDataSendEvent;
use Common\Jobs\User\UserBehaviorStatisticsJob;
use Common\Models\Approve\ManualApproveLog;
use Common\Models\Order\ContractAgreement;
use Common\Services\Config\LoanMultipleConfigServer;
use Common\Services\NewClm\ClmServer;
use Common\Services\OrderAgreement\OrderAgreementServer;
use Common\Utils\Data\DateHelper;
use Common\Utils\Data\MoneyHelper;
use Common\Utils\Host\HostHelper;
use Common\Utils\Lock\LockRedisHelper;
use DB;
use Common\Models\Config\LoanMultipleConfig;
use Common\Models\User\UserInitStep;

class OrderServer extends \Common\Services\Order\OrderServer
{
    public function index(User $user, $param)
    {
        $param['user_id'] = $user->id;
        $datas = Order::model()->getList($param);
        foreach ($datas as $data) {
            $data->setScenario(Order::SCENARIO_INDEX)->getText();
            if(!in_array($user->merchant_id, [1, 4]) && isset($data->signed_time) && $time = strtotime($data->signed_time)){
                $data->signed_time = date("d/M/Y", $time);
            }
            if(!in_array($user->merchant_id, [1, 4]) && isset($data->created_at) && $time = strtotime($data->created_at)){
                $data->created_at = date("d/M/Y", $time);
            }
            unset($data->orderDetails);
            unset($data->lastRepaymentPlan);
        }
        return $datas;
    }

    public function detail(User $user, $param)
    {
        $data = Order::model()->getOneByUser($param['order_id'], $user->id);
        if (!$data) {
            return $this->outputException('该记录不存在');
        }
        foreach ($data->contractAgreements as $agreements) {
            $agreements->setScenario(ContractAgreement::SCENARIO_LIST)->getText();
        }
        $paidTime = $data->paid_time;
        $data->setScenario(Order::SCENARIO_DETAIL)->getText();
        if(!in_array($user->merchant_id, [1, 4]) && $data->appointment_paid_time && $time = strtotime($data->appointment_paid_time)){
            $data->appointment_paid_time = date("d/M/Y", $time);
        }
        if(!in_array($user->merchant_id, [1, 4]) && $data->paid_time && $time = strtotime($data->paid_time)){
            $data->paid_time = date("d/M/Y", $time);
        }
        if(!in_array($user->merchant_id, [1, 4]) && $data->created_at && $time = strtotime($data->created_at)){
            $data->created_at = date("d/M/Y", $time);
        }
        /** 订单详情 未放款应还金额置0 */
        //$data->receivable_amount = empty($paidTime) ? MoneyHelper::round2point(0) : $data->receivable_amount;
        return $data;
    }

    public function create($user, $param)
    {
        /** 订单创建拦截 */
        OrderCheckServer::server()->canCreateOrder();
        if (!LockRedisHelper::helper()->orderCreate($user->id)) {
            return $this->outputException('操作过于频繁, 请稍后再试!');
        }
        /** @var User $user */
        /** 如果存在进行中订单 则不创建 */
        if ($order = $user->order && OrderCheckServer::server()->hasGoingOrder($user->order)) {
            return $order;
        }

        // 根据后台配置定制审批状态
        $status = \Common\Services\Order\OrderServer::server()->getOrderCreateStatus($user);
        $param['status'] = $status;

        $isRepeat = false;
        $param['quality'] = Order::QUALITY_NEW;
        if ($user->getRealQuality()) {
            $param['quality'] = Order::QUALITY_OLD;
            $isRepeat = true;
        }

        /** @var Order $order */
        $order = DB::transaction(function () use ($user, $param, $isRepeat) {
            $order = Order::model()->create($param);

            //更新用户为老用户
            if ($isRepeat && ($user->quality != User::QUALITY_OLD) && !UserServer::server()->qualityToOld($order->user_id)) {
                throw new \Exception('user to old failure');
            }

            return $order;
        });
        if (!$order) {
            return $this->outputException('订单创建失败');
        }
        return $order;
    }

    public function checkAmountDays($user, $principal, $loanDays){
        $amount = LoanMultipleConfigServer::server()->getLoanAmountRange($user);
        $days = LoanMultipleConfigServer::server()->getLoanDaysRange($user);
        if(!in_array($loanDays, $days)){
            return false;
        }
        if(!in_array($principal, $amount)){
            return false;
        }
        return true;
    }

    public function update($principal, $loanDays)
    {
        /** @var User $user */
        $user = \Auth::user();
        /*if (!$user->getIsCompleted()) {
            return $this->outputException(t("资料未完善", 'messages'));
        }*/
        if (!$this->checkAmountDays($user, $principal, $loanDays)){
            return $this->outputException("The application amount does not match");
        }
        if (!$user->order) {
            return $this->outputException('无进行中订单');
        }
        $order = $user->order;
        //OrderCheckServer::server()->canUpdateOrder($order, $user);签约订单（废弃）
        $order = DB::transaction(function () use ($order, $principal, $loanDays) {
            $order->setScenario(Order::SCENARIO_UPDATE)->saveModel([
                'principal' => $principal,
                'loan_days' => $loanDays,
            ]);
            OrderDetailServer::server()->saveByOrderUpdate($order);
            return $order;
        });
        return $this->outputSuccess('订单更新成功');
    }

    /**
     * 订单签约(确认订单)
     * @param User $user
     * @param array $params
     * @return OrderServer|void
     * @throws \Common\Exceptions\ApiException
     */
    public function sign(User $user, $params = [])
    {
        /** @var User $user */
        if (!$user->getIsCompleted()) {
            # 缺少认证资料 清空用户步骤从新接入
            UserInitStep::model()->where("user_id", $user->id)->delete();
            return $this->outputException(t("资料未完善", 'messages'));
        }
        if (!$order = $user->order) {
            return $this->outputException('无进行中订单');
        }
        OrderCheckServer::server()->canSignOrder($order, $user);

        /** 签约后默认都需要人审电审 */
        $params['approve_push_status'] = Order::PUSH_STATUS_NO_NEED;
        $params['manual_check'] = Order::MANUAL_CHECK_REQUIRE;
        $params['call_check'] = Order::CALL_CHECK_REQUIRE;

        DB::beginTransaction();

        $order->loan_days = array_get($params, 'loan_days');
        $order->principal = array_get($params, 'principal');
        $params['service_charge'] = $order->principal * LoanMultipleConfigServer::server()->getServiceChargeRate($user, $order->loan_days); //砍头费
        $params['service_charge'] = $params['service_charge']*(1-ClmServer::server()->getInterestDiscount($order->user)/100); //砍头费打折
        $params['withdrawal_service_charge'] = OrderServer::server()->getWithdrawalServiceCharge($order); //线下取款手续费

        /** 订单签约&提交机审(TODO 判断机审env开关状态) */
        if (!(SystemApproveServer::server()->envSystemApproveExec() ? OrderServer::server()->signAndSystemApprove($order, $params) :
                OrderServer::server()->signAndManualApprove($order, $params)) ||
            /** 保存附加信息到OrderDetail */
            !OrderDetailServer::server()->saveByCreateOrder($order, $params) ||
            /** 更新最新银行卡信息到OrderDetail */
            !OrderDetailServer::server()->saveOrderBank($order)) {
            DB::rollBack();
            return $this->outputException('Failure to sign an order,pls log out and reapply');
        }
        DB::commit();
        $order->refresh();
        /** 菲律宾无需NBFC上报 */
        //event(new NbfcReportEvent($order->id, NbfcReportConfig::REPORT_NODE_SIGN));
        /** 机审事件=>队列，暂不跑机审队列，单靠定时任务 */
        //event(new SystemApproveEvent($order->id));
        /** 统计埋点数据 UserBehaviorStatistics */
        dispatch(new UserBehaviorStatisticsJob($order));
        /** 生成合同Async */
        event(new OrderAgreementEvent($order->id, ContractAgreement::CASHNOW_LOAN_CONTRACT, ''));
        /** 推送 */
        event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_INTO_APPROVE));
        event(new RiskDataSendEvent($order->user_id, RiskDataSendEvent::NODE_ORDER_CREATE));
        /** 新复贷用户订单申请拦截预警推送 */
        event(new OrderCreateEvent($order->id, $order->merchant_id, $order->quality));

        return $this->outputSuccess('订单签约成功');
    }

    /**
     * @param User $user
     * @param $data
     * @return OrderServer
     */
    public function cancel(User $user, $data)
    {
        if (!$user->order) {
            return $this->outputError('无进行中订单');
        }
        if (!OrderCheckServer::server()->canCancelOrder($user->order)) {
            return $this->outputError('当前借款不可取消');
        }
        if (!OrderServer::server()->userCancel($user->order->id)) {
            return $this->outputError('订单取消失败');
        }
        event(new RiskDataSendEvent($user->id, RiskDataSendEvent::NODE_ORDER_CANCEL));
        return $this->outputSuccess('订单取消成功');
    }

    /**
     * 科目计算
     * @param Order $order
     * @param $loanDays
     * @param $principal
     * @return Order|\Illuminate\Database\Eloquent\Model|null|object
     */
    public function calculate($order, $loanDays, $principal)
    {
        $order->loan_days = $loanDays;
        $order->principal = $principal;
        $order->service_charge_fee = strval($order->getProcessingFee()); //服务费
        $order->interest_fee = strval($order->interestFee()); //利息
        $order->receivable_amount = strval($order->principal + $order->interest_fee); //应还金额
        $order->appointment_paid_time = Carbon::parse($order->getAppointmentPaidTime())->format('d-m-Y'); //应还金额
        return $order;
    }

    /**
     * 重新提交资料 提交订单
     * @param User $user
     * @return Order|OrderServer
     * @throws \Common\Exceptions\ApiException
     */
    public function replenish(User $user)
    {
        $order = $user->order;
        if (!$order || $order->status != Order::STATUS_REPLENISH) {
            return $this->outputError('订单状态不正确，请重试!');
        }

        $this->replenishFinish($order->id);

        return $order;
    }

    public function agreement($orderId, $type, $isPreview = false)
    {
        return OrderAgreementServer::server()->generate($orderId, $type, false, $isPreview);
    }

    /**
     * 判断能否进行续期
     * @param $user
     * @param $orderId
     * @return Order|bool|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function canRenewal($user, $orderId)
    {
        $order = $data = Order::model()->getOneByUser($orderId, $user->id);
        if (!OrderCheckServer::server()->canRenewalOrder($order)) {
            return false;
        }
        return $order;
    }

    /**
     * 判断是否有订单（有进行中或者已完成）
     *
     * @param \Common\Models\User\User $user
     * @return bool
     */
    public function hasOrder(\Common\Models\User\User $user)
    {
        if ($user->getRealQuality() == User::QUALITY_OLD) {
            return true;
        }
        if ($user->order && $user->order->isNotComplete()) {
            return true;
        }
        return false;
    }

    public function getLastOrder(User $user)
    {
        # order
        if ($order = $user->order) {
            //判断无订单状态
            if ($order->checkHideOrder()) {
                return null;
            } else {
                $order->setScenario(Order::SCENARIO_HOME)->getText();
                if (
                    $order->status == Order::STATUS_SYSTEM_PAY_FAIL &&
                    $order->lastTradeLog
                ) {
                    $order->lastTradeLog->trade_desc = OrderServer::SYSTEM_PAY_FAIL_MSG;
                }
                $order->waitAuthMsg = '';
                $order->waitAuths = OrderServer::server()->getSuppleTitleIcon($order);

                $order->rejectedTimeLeft = $order->isRejected() ? (DateHelper::ms($order->reject_time) + (OrderServer::server()->getRejectLastDays($order) + 1) * 86400000 - DateHelper::ms()) : 0;

                //$order->tip = TipServer::server()->getOrderTip($order);
                unset($order->order_detail);
            }
            $order->withdrawalNumber = $order->withdraw_no ?? "---";
            unset($order->withdraw_no);
        }
        unset($order->user);
        return $order;
    }

    public function getSuppleTitleIcon(Order $order)
    {
        if ($order->status != Order::STATUS_REPLENISH) {
            return [];
        }
        $waitAuths = ManualApproveLog::model()->waitAuths($order);
        if (!$waitAuths) {
            return [];
        }
        $waitAuthList = [];
        foreach ($waitAuths as $waitAuthName) {
            if ($authName = array_get(ManualApproveLog::SUPPLEMENT_AUTHS, $waitAuthName)) {
                $waitAuthList[$authName] = [
                    'title' => array_get(AuthServer::AUTH_CONFIG, $authName . '.title'),
                    'icon' => HostHelper::getDomain() . array_get(AuthServer::AUTH_CONFIG, $authName . '.icon'),
                ];
            }
        }
        return array_values($waitAuthList);
    }

    public function orderRepaymentPlan(User $user, $param)
    {
        $orderId = array_get($param, 'order_id');
        $order = Order::model()->getOne($orderId);
        if (!$order) {
            return $this->outputException('该记录不存在');
        }
        foreach ($order->repaymentPlans as &$repaymentPlan) {
            $repaymentPlan->setScenario(RepaymentPlan::SCENARIO_LIST)->getText();
        }
        return $order->repaymentPlans;
    }

    /**
     * 订单尾期减免
     */
    public function orderReduction(User $user, $param)
    {
        /*$orderId = array_get($param, 'order_id');
        $order = Order::model()->getOne($orderId);
        if (!$order) {
            return $this->outputException(t('该记录不存在', 'exception'));
        }
        $orderInstallment = OrderDetail::model()->getInstallment($order);
        if (!$orderInstallment) {
            return $this->outputException(t('非分期订单'));
        }
        $orderProgressingRepaymentPlanCount = (new RepaymentPlan)->getProgressingRepaymentPlanCount($orderId);
        if($orderProgressingRepaymentPlanCount != 1){
            return $this->outputException(t('未到最后一期' . $orderProgressingRepaymentPlanCount));
        }
        try{
            $result = RepaymentPlanServer::server()->reductionRepaySuccess($order);
        } catch (\Exception $e) {
            return $this->outputException('减免失败');
        }
        if (!$result) {
            return $this->outputException('还款失败');
        }

        return $this->outputSuccess('提交成功');*/
    }

    /**
     * 复贷次数
     * @param User $user
     * @return type
     */
    public function getReloanCount(User $user) {
        $query = Order::query();
        $query->where('status', Order::STATUS_FINISH)->where("user_id", $user->id);
        return $query->count();
    }

    /**
     * 根据贷款天数获取服务费
     * @param type $merchantId
     * @param type $repeatLoanCnt
     * @param type $loanDays
     * @return type
     */
    public function getServiceCharge($merchantId, $repeatLoanCnt, $loanDays){
        $serviceCharge = LoanMultipleConfig::getConfigByCnt($merchantId, $repeatLoanCnt, LoanMultipleConfig::FIELD_PROCESSING_RATE);

        $days = LoanMultipleConfig::getConfigByCnt($merchantId, $repeatLoanCnt, LoanMultipleConfig::FIELD_LOAN_DAYS_RANGE);
        if ($key = array_search($loanDays, $days)) {
            $serviceCharge = $serviceCharge[$key];
        }else{
            return $serviceCharge[0];
        }
        return $serviceCharge;
    }

    public function getRenewalRate($user, $loanDays){
        $renewalRate = LoanMultipleConfig::getConfigByCnt($user->merchant_id, $user->getRepeatLoanCnt(), LoanMultipleConfig::FIELD_LOAN_RENEWAL_RATE);
        $days = LoanMultipleConfig::getConfigByCnt($user->merchant_id, $user->getRepeatLoanCnt(), LoanMultipleConfig::FIELD_LOAN_DAYS_RANGE);
        if ($key = array_search($loanDays, $days)) {
            $renewalRate = $renewalRate[$key];
        }else{
            return $renewalRate[0];
        }
        return $renewalRate;
    }
}
