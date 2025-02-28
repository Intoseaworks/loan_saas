<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/12
 * Time: 16:23
 */

namespace Common\Services\Order;

use Admin\Services\Activity\ActivitiesRecordServer;
use Admin\Services\Activity\ActivityAwardServer;
use Admin\Services\Coupon\CouponReceiveServer;
use Api\Models\Order\OrderDetail;
use Api\Services\Order\OrderDetailServer;
use Approve\Admin\Services\Rule\ApproveRuleService;
use Carbon\Carbon;
use Common\Events\Order\OrderStatusChangeEvent;
use Common\Events\Risk\RiskDataSendEvent;
use Common\Jobs\Push\Sms\SmsByCommonJob;
use Common\Models\Activity\ActivitiesRecord;
use Common\Models\BankCard\BankCardPeso;
use Common\Models\Config\Config;
use Common\Models\Coupon\CouponReceive;
use Common\Models\Merchant\App;
use Common\Models\Order\Order;
use Admin\Models\Order\Order as AdminOrder;
use Common\Models\Order\OrderLog;
use Common\Models\Order\RepaymentPlan;
use Common\Models\Risk\RiskBlacklist;
use Common\Models\User\User;
use Common\Models\User\UserAuth;
use Common\Models\User\UserInitStep;
use Common\Models\User\UserInviteFriend;
use Common\Models\User\UserThirdData;
use Common\Services\BaseService;
use Common\Services\NewClm\ClmServer;
use Common\Utils\Data\DateHelper;
use Common\Utils\Data\MoneyHelper;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Sms\SmsHelper;
use Illuminate\Database\Eloquent\Builder;
use Common\Models\Repay\RepayDetail;
use Common\Services\Config\LoanMultipleConfigServer;
use Common\Jobs\Crm\UpdateCustomerJob;

class OrderServer extends BaseService
{
    const SYSTEM_PAY_FAIL_MSG = '收款银行卡异常，点击【换卡重新放款】，重新放款';

    /************************************************************************************************************************
     *  订单状态流转begin
     ************************************************************************************************************************/

    /**
     * 订单人工确认待放款
     * @param $orderId
     * @param array $params
     * @return bool
     */
    public function toSign($orderId, $params = [])
    {
        $params = array_only($params, ['pay_channel', 'pay_type']);
        $params['confirm_pay_time'] = $this->getDateTime();
        return $this->changeStatus($orderId, Order::WAIT_SIGN, Order::STATUS_SIGN, $params);
    }

    public function rejectManualToCall($orderId)
    {
        $params = [
            'manual_time' => $this->getDateTime(),
            'approve_push_status' => Order::PUSH_STATUS_WAITING,
            'call_check' => Order::CALL_CHECK_REQUIRE,
            'manual_result' => Order::MANUAL_RESULT_PASS,
        ];
        return $this->changeStatus($orderId, Order::STATUS_MANUAL_REJECT, Order::STATUS_WAIT_CALL_APPROVE, $params);
    }

    public function rejectManualToPass($orderId)
    {
        $params = [
            'manual_time' => $this->getDateTime(),
            'pass_time' => $this->getDateTime(),
            'manual_result' => Order::MANUAL_RESULT_PASS,
            'call_result' => Order::CALL_RESULT_PASS,//人审通过，代表电审自动通过
        ];
        return $this->changeStatus($orderId, Order::STATUS_MANUAL_REJECT, Order::STATUS_MANUAL_PASS, $params);
    }

    public function rejectCallToPass($orderId)
    {
        $params = [
            'manual_time' => $this->getDateTime(),
            'pass_time' => $this->getDateTime(),
            'call_result' => Order::CALL_RESULT_PASS,
        ];
        return $this->changeStatus($orderId, [Order::STATUS_MANUAL_REJECT], Order::STATUS_MANUAL_PASS, $params);
    }


    /**
     * 订单签约&提交机审
     * @param Order $order
     * @param array $params
     * @return bool
     */
    public function signAndSystemApprove($order, $params = [])
    {
        $order->refresh();
        $params = array_only($params, ['loan_days', 'principal', 'service_charge', 'withdrawal_service_charge', 'approve_push_status', 'manual_check', 'call_check']);
        $params['signed_time'] = $this->getDateTime();

        /** CLM模块冻结额度 */
        $freezeRes = ClmServer::server()->getOrInitCustomer($order->user)->freeze($params['principal']);
        if (!$freezeRes) {
            return false;
        }
        if($order->merchant_id == \Common\Models\Merchant\Merchant::getId('u1')){
            dispatch(new \Common\Jobs\Risk\RepairAppDataJob($order->id, $order->user_id));
        }
        return $this->changeStatus($order->id, Order::STATUS_CREATE, Order::STATUS_WAIT_SYSTEM_APPROVE, $params);
    }

    /**
     * 订单签约&扭转人审
     * @param Order $order
     * @param array $params
     * @return bool
     */
    public function signAndManualApprove($order, $params = [])
    {
        $order->refresh();
        $params = array_only($params, ['loan_days', 'principal', 'service_charge', 'withdrawal_service_charge', 'approve_push_status', 'manual_check', 'call_check']);
        $params['signed_time'] = $this->getDateTime();

        /** CLM模块冻结额度 */
        $freezeRes = ClmServer::server()->getOrInitCustomer($order->user)->freeze($params['principal']);
        if (!$freezeRes) {
            return false;
        }
        return $this->changeStatus($order->id, Order::STATUS_CREATE, Order::STATUS_WAIT_MANUAL_APPROVE, $params);
    }

    /**
     * 机审中
     * 待系统机审 => 机审中
     * @param $orderId
     * @return array|bool
     */
    public function systemApproving($orderId)
    {
        return $this->changeStatus($orderId, Order::STATUS_WAIT_SYSTEM_APPROVE, Order::STATUS_SYSTEM_APPROVING);
    }

    /**
     * 机审通过
     * 机审中 => 人工审核(初审or电审) or 机审通过
     * 若无配置 人工审批流程 则流转为机审通过
     * @param $orderId
     * @return array|bool
     */
    public function systemToManualOrPass($orderId)
    {
        $order = Order::model()->getOne($orderId);

        $toStatus = $this->getOrderSystemPassStatus($order);
        $params = [];
        $params['system_time'] = $this->getDateTime();
        $params['service_charge'] = $order->principal * LoanMultipleConfigServer::server()->getServiceChargeRate($order->user, $order->loan_days);
        $params['service_charge'] = $params['service_charge']*(1-ClmServer::server()->getInterestDiscount($order->user)/100); //砍头费打折
        $approvePass = false;
        if ($toStatus == Order::STATUS_WAIT_MANUAL_APPROVE) {
            $params['approve_push_status'] = Order::PUSH_STATUS_WAITING;
        } elseif ($toStatus == Order::STATUS_WAIT_CALL_APPROVE) {
            $params['approve_push_status'] = Order::PUSH_STATUS_WAITING;
            $params['manual_result'] = Order::CALL_RESULT_PASS;
        } elseif ($toStatus == Order::STATUS_SYSTEM_PASS) {
            $params['manual_result'] = Order::MANUAL_RESULT_PASS;
            $params['call_result'] = Order::CALL_RESULT_PASS;
            $params['pass_time'] = $this->getDateTime();
            $approvePass = true;
        }

        $res = $this->changeStatus($orderId,
            Order::STATUS_SYSTEM_APPROVING,
            $toStatus,
            $params
        );

        /*if ($res && $approvePass) {
            event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_APPROVE_PASS));
        }*/

        return $approvePass;
    }

    /**
     * 机审拒绝
     * 机审中 => 系统审核拒绝
     * @param Order $order
     * @param $refusalCode
     * @return array|bool
     * @throws \Exception
     */
    public function systemReject($order, $refusalCode)
    {
        $order->refresh();
        $params = [
            'system_time' => $this->getDateTime(),
            'reject_time' => $this->getDateTime(),
            'refusal_code' => $refusalCode,
        ];
        //记录重借等待天数
        OrderDetailServer::server()->saveRejectedDays($order);
        /** CLM模块解冻额度 */
        ClmServer::server()->getOrInitCustomer($order->user)->unfreeze($order->principal);
        return $this->changeStatus($order->id, Order::STATUS_SYSTEM_APPROVING, Order::STATUS_SYSTEM_REJECT, $params);
    }

    public function systemRevertToWait($orderId)
    {
        return $this->changeStatus($orderId, Order::STATUS_SYSTEM_APPROVING, Order::STATUS_WAIT_SYSTEM_APPROVE);
    }

    /**
     * 审批中机审打分拒绝
     *
     * @param $orderId
     * @param $userId
     * @return bool
     */
    public function approveSystemReject($orderId, $userId)
    {
        // 清空用户所有认证项
        UserAuth::model()->clearAllAuth($userId);

        return $this->changeStatus(
            $orderId,
            [Order::STATUS_WAIT_MANUAL_APPROVE, Order::STATUS_WAIT_CALL_APPROVE, Order::STATUS_WAIT_TWICE_CALL_APPROVE],
            Order::STATUS_SYSTEM_REJECT
        );
    }

    /**
     * 人审通过
     * 待人工审核 => 人工审核通过待放款
     * @param $orderId
     * @return bool
     */
    public function manualPass($orderId)
    {
        $params = [
            'manual_time' => $this->getDateTime(),
            'pass_time' => $this->getDateTime(),
            'manual_result' => Order::MANUAL_RESULT_PASS,
            'call_result' => Order::CALL_RESULT_PASS,//人审通过，代表电审自动通过
        ];
        return $this->changeStatus($orderId, Order::STATUS_WAIT_MANUAL_APPROVE, Order::STATUS_MANUAL_PASS, $params);
    }

    /**
     * 电审通过
     * 待人工电审 => 人工电审通过待放款
     * @param $orderId
     * @return bool
     */
    public function callToPass($orderId)
    {
        $params = [
            'manual_time' => $this->getDateTime(),
            'pass_time' => $this->getDateTime(),
            'call_result' => Order::CALL_RESULT_PASS,
        ];
        return $this->changeStatus($orderId, [Order::STATUS_WAIT_CALL_APPROVE, Order::STATUS_WAIT_TWICE_CALL_APPROVE], Order::STATUS_MANUAL_PASS, $params);
    }

    /**
     * 人审通过
     * 待人工审核 => 待电审
     *
     * @param $orderId
     * @return bool
     */
    public function manualToCall($orderId)
    {
        $params = [
            'manual_time' => $this->getDateTime(),
            'approve_push_status' => Order::PUSH_STATUS_WAITING,
            'call_check' => Order::CALL_CHECK_REQUIRE,
            'manual_result' => Order::MANUAL_RESULT_PASS,
        ];
        return $this->changeStatus($orderId, Order::STATUS_WAIT_MANUAL_APPROVE, Order::STATUS_WAIT_CALL_APPROVE, $params);
    }

    /**
     * 电审流转到电二审
     * 待电审=>待电二审
     *
     * @param $orderId
     * @return bool
     */
    public function manualSecondCall($orderId)
    {
        $params = [
            'approve_push_status' => Order::PUSH_STATUS_WAITING,
        ];
        return $this->changeStatus($orderId, Order::APPROVAL_CALL_STATUS, Order::STATUS_WAIT_TWICE_CALL_APPROVE, $params);
    }

    /**
     * 人审拒绝
     * 待人工审核 => 人工审核拒绝
     * @param Order $order
     * @param $userId
     * @param $refusalCode
     * @return bool
     * @throws \Exception
     */
    public function manualReject($order, $userId, $refusalCode)
    {
        $order->refresh();
        $params = [
            'manual_time' => $this->getDateTime(),
            'reject_time' => $this->getDateTime(),
            'refusal_code' => $refusalCode,
        ];
        // 清空用户所有认证项
        UserAuth::model()->clearAllAuth($userId);
        /** CLM模块解冻额度 */
        ClmServer::server()->getOrInitCustomer($order->user)->unfreeze($order->principal);
        //记录重借等待天数
        OrderDetailServer::server()->saveRejectedDays($order);
        /** 根据拒贷码处理后续逻辑 */
        ApproveRuleService::server()->handleByOperation($order, $refusalCode);
        return $this->changeStatus($order->id, [
            Order::STATUS_WAIT_MANUAL_APPROVE,
            Order::STATUS_WAIT_CALL_APPROVE,
            Order::STATUS_WAIT_TWICE_CALL_APPROVE,
        ], Order::STATUS_MANUAL_REJECT, $params);
    }

    /**
     * 重新提交资料
     * 待人工审核 => 待补充资料
     * @param $orderId
     * @return bool
     */
    public function manualReplenish($orderId)
    {
        $params = [
            'approve_push_status' => Order::PUSH_STATUS_WAITING,
        ];
        return $this->changeStatus($orderId, Order::STATUS_WAIT_MANUAL_APPROVE, Order::STATUS_REPLENISH, $params);
    }

    /**
     * 重新提交资料完成
     * 待补充资料 => 待人工审核
     * @param $orderId
     * @return bool
     */
    public function replenishFinish($orderId)
    {
        $params = [
            'approve_push_status' => Order::PUSH_STATUS_WAITING,
        ];
        return $this->changeStatus($orderId, Order::STATUS_REPLENISH, Order::STATUS_WAIT_MANUAL_APPROVE, $params);
    }

    /**
     * 放款中
     * @param $orderId
     * @return bool
     */
    public function paying($orderId)
    {
        return $this->changeStatus($orderId, Order::WAIT_PAY_STATUS, Order::STATUS_PAYING);
    }

    /**
     * 人工出款成功待还款
     * 人工审核通过待放款 => 人工出款成功待还款
     * @param Order $order
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public function manualPaid($order, $params = [])
    {
        $order->refresh();
        $fromStatus = array_merge(Order::WAIT_PAY_STATUS, [Order::STATUS_PAYING, Order::STATUS_SYSTEM_PAY_FAIL, Order::STATUS_MANUAL_PAY_FAIL]);
        /** CLM模块使用额度 */
        ClmServer::server()->getOrInitCustomer($order->user)->use($order->principal);
        return $this->changeStatus($order->id, $fromStatus, Order::STATUS_MANUAL_PAID, $params);
    }

    /**
     * 人工放款失败 MANUAL_PAY_FAIL
     * 人工审核通过待放款 => 人工出款失败
     * @param $orderId
     * @return bool
     */
    public function manualPayFail($orderId)
    {
        $fromStatus = array_merge(Order::WAIT_PAY_STATUS, [Order::STATUS_PAYING, Order::STATUS_SYSTEM_PAY_FAIL, Order::STATUS_MANUAL_PAY_FAIL]);
        return $this->changeStatus($orderId, $fromStatus, Order::STATUS_MANUAL_PAY_FAIL);
    }

    /**
     * 系统放款成功
     * 人工审核通过待放款|放款处理中 => 系统出款成功
     * @param Order $order
     * @param array $params
     * @return bool
     * @throws \Throwable
     */
    public function systemPaid($order, $params = [])
    {
        $order->refresh();
        $fromStatus = array_merge(Order::WAIT_PAY_STATUS, [Order::STATUS_PAYING, Order::STATUS_SYSTEM_PAY_FAIL, Order::STATUS_MANUAL_PAY_FAIL]);
        /** CLM模块使用额度 */
        ClmServer::server()->getOrInitCustomer($order->user)->use($order->principal);
        return $this->changeStatus($order->id, $fromStatus, Order::STATUS_SYSTEM_PAID, $params);
    }

    /**
     * 系统放款失败
     * 人工审核通过待放款|放款处理中 => 系统出款失败
     * @param $orderId
     * @return bool
     */
    public function systemPayFail($orderId)
    {
        $fromStatus = array_merge(Order::WAIT_PAY_STATUS, [Order::STATUS_PAYING, Order::STATUS_SYSTEM_PAY_FAIL, Order::STATUS_SYSTEM_PAID]);
        return $this->changeStatus($orderId, $fromStatus, Order::STATUS_SYSTEM_PAY_FAIL);
    }

    /**
     * 系统出款失败重试
     * 系统出款失败 => 待放款(签约)
     * @param $orderId
     * @return bool
     * @throws \Exception
     */
    public function payFailAfreshPay($orderId)
    {
        return $this->changeStatus($orderId, Order::STATUS_SYSTEM_PAY_FAIL, Order::STATUS_SIGN);
    }

    /**
     * 回款处理中
     * @param $orderId
     * @return bool
     */
    public function repaying($orderId)
    {
        return $this->changeStatus($orderId, Order::WAIT_REPAYMENT_STATUS, Order::STATUS_REPAYING);
    }

    /**
     * 还款结清
     * [系统出款成功待还款、人工出款成功待还款、逾期] => 结清还款
     * 订单逾期状态可正常结清 根据实际还款日期计算
     * @param Order $order
     * @param array $params
     * @return bool
     * @throws \Throwable
     */
    public function repayFinish($order, $params = [])
    {
        $overdueDays = $params['overdue_days'];
        $toStatus = $overdueDays > 0 ? Order::STATUS_OVERDUE_FINISH : Order::STATUS_FINISH;
        //待还款 + 还款中
        $fromStatus = array_merge(Order::WAIT_REPAYMENT_STATUS, [Order::STATUS_REPAYING], Order::FINISH_STATUS);
        return $this->changeStatus($order->id, $fromStatus, $toStatus);
    }

    /**
     * 部分还款计划完结订单流转
     * 还款中 => [人工出款成功待还款、系统出款成功待还款]
     * @param $orderId
     * @param $inOverdue
     * @return bool
     * @throws \Exception
     */
    public function revertToPaid($orderId, $inOverdue = false)
    {
        //待还款 + 还款中
        $fromStatus = array_merge(Order::WAIT_REPAYMENT_STATUS, [Order::STATUS_REPAYING]);

        $toStatus = [
            Order::STATUS_MANUAL_PAID,
            Order::STATUS_SYSTEM_PAID,
            Order::STATUS_OVERDUE
        ];

        // 如果当前应还还款计划处于逾期，则toStatus加入逾期相关状态
        if ($inOverdue) {
            $toStatus = array_merge($toStatus, Order::WAIT_REPAYMENT_STATUS);
        }

        $orderLog = OrderLog::query()->where('order_id', $orderId)
            ->whereIn("to_status", $toStatus)
            ->latest()
            ->first();

        if (!$orderLog) {
            throw new \Exception("订单状态流转错误，trade_log 无待还款状态");
        }
        $toStatus = $orderLog->to_status;

        return $this->changeStatus($orderId, $fromStatus, $toStatus);
    }

    /**
     * 流转回上一个状态
     * @param $orderId
     * @param array $allowToStatus 允许的toStatus，不传为全部允许
     * @return bool
     */
    public function revertStatus($orderId, $allowToStatus = [])
    {
        $order = Order::find($orderId);
        $lastOrderLog = $order->lastOrderLog;

        if (!$lastOrderLog || !in_array($lastOrderLog->from_status, array_keys(Order::STATUS_ALIAS))) {
            return false;
        }
        $toStatus = $lastOrderLog->from_status;
        if ($allowToStatus && !in_array($toStatus, $allowToStatus)) {
            $notAllow = true;
            foreach ($order->orderLog as $historyLog) {
                if (in_array($historyLog->from_status, $allowToStatus)) {
                    $toStatus = $historyLog->from_status;
                    $notAllow = false;
                    continue;
                }
            }
            if ($notAllow) {
                return false;
            }
        }

        return $this->changeStatus($orderId, Order::ALLOW_REVERT_STATUS, $toStatus);
    }

    /**
     * 人工取消
     * @param Order $order
     * @param $fromStatus
     * @return bool
     * @throws \Exception
     */
    public function manualCancel($order, $fromStatus)
    {
        $params = [
            'cancel_time' => $this->getDateTime(),
        ];
        /** CLM模块解冻额度 */
        ClmServer::server()->getOrInitCustomer($order->user)->unfreeze($order->principal);
        event(new RiskDataSendEvent(null, RiskDataSendEvent::NODE_ORDER_CANCEL, $order->id));
        return $this->changeStatus($order->id, $fromStatus, Order::STATUS_MANUAL_CANCEL, $params);
    }

    /**
     * 人工取消
     * @param Order $order
     * @param $fromStatus
     * @return bool
     * @throws \Exception
     */
    public function systemCancel($order, $fromStatus)
    {
        $params = [
            'cancel_time' => $this->getDateTime(),
        ];
        /** CLM模块解冻额度 */
        ClmServer::server()->getOrInitCustomer($order->user)->unfreeze($order->principal);
        event(new RiskDataSendEvent(null, RiskDataSendEvent::NODE_ORDER_CANCEL, $order->id));
        return $this->changeStatus($order->id, $fromStatus, Order::STATUS_SYSTEM_CANCEL, $params);
    }

    /**
     * 人工取消无证件号证件类型首贷订单
     * @param Order $order
     * @param $fromStatus
     * @return bool
     * @throws \Exception
     */
    public function systemCancelNoCardNewUser($order, $fromStatus)
    {
        $params = [
            'cancel_time' => $this->getDateTime(),
        ];
        //对于生产数据库中，需要额外处理一下U1，对于U1的订单，在取消的时候还需要多做一步，就是把订单对应user_id的user_init_step清空。U1当前最后一笔订单状态是system_cancel的需要手动把user_init_step清空
//        $merchant = $order->merchant_id;
//        if ( 3 == $merchant ){
        $userId = $order->user_id;
        $lastOrder = Order::whereUserId($userId)->orderByDesc('id')->first();
        if ( Order::STATUS_SYSTEM_CANCEL == $lastOrder->status || $lastOrder->id == $order->id ){
            UserInitStep::whereUserId($userId)->delete();
        }
//        }
        event(new RiskDataSendEvent(null, RiskDataSendEvent::NODE_ORDER_CANCEL, $order->id));
        return $this->changeStatus($order->id, $fromStatus, Order::STATUS_SYSTEM_CANCEL, $params);
    }

    /**
     * 出款失败[人工出款失败|系统出款失败] => 已签约
     * @param $orderId
     * @return bool
     */
    public function manualPayFailToManualPass($orderId)
    {
        return $this->changeStatus($orderId, Order::PAY_FAIL_STATUS, Order::STATUS_SIGN);
    }

    /**
     * 用户取消
     * @param $orderId
     * @return bool
     */
    public function userCancel($orderId)
    {
        $fromStatus = Order::CAN_USER_CANCEL;
        $params = [
            'cancel_time' => $this->getDateTime(),
        ];
        return $this->changeStatus($orderId, $fromStatus, Order::STATUS_USER_CANCEL, $params);
    }

    /**
     * 逾期
     * @param $orderId
     * @return bool
     */
    public function beOverdue($orderId)
    {
        $fromStatus = Order::BE_OVERDUE_STATUS;
        return $this->changeStatus($orderId, $fromStatus, Order::STATUS_OVERDUE,
            ['overdue_time' => DateHelper::dateTime()]);
    }

    /**
     * 坏账
     * @param $orderId
     * @return bool
     */
    public function collectionBad($orderId)
    {
        $fromStatus = Order::BE_OVERDUE_STATUS;
        return $this->changeStatus($orderId, $fromStatus, Order::STATUS_COLLECTION_BAD,
            ['bad_time' => DateHelper::dateTime()]);
    }

    /**
     * 撤回坏账
     * @param $orderId
     * @return bool
     */
    public function recallCollectionBad($orderId)
    {
        $fromStatus = Order::STATUS_COLLECTION_BAD;
        return $this->changeStatus($orderId, $fromStatus, Order::STATUS_OVERDUE);
    }

    /**
     * 状态修改
     * @param $orderId
     * @param $fromStatus
     * @param $toStatus
     * @param $params
     * @return bool
     */
    public function changeStatus($orderId, $fromStatus, $toStatus, $params = [])
    {
        $order = Order::whereId($orderId)->whereIn('status', (array)$fromStatus)->first();
        if (!$order) {
            $orderFormStatus = optional(Order::find($orderId))->status;
            DingHelper::notice("order_id:{$orderId} formStatus:{$orderFormStatus} toStatus:{$toStatus}", '订单状态流转异常', DingHelper::AT_CXS);
            throw new \Exception('订单状态流转异常['.$orderId.' : '.$orderFormStatus.' => '.$toStatus.']');
        }

        /** 订单状态流转事件 */
        event(new OrderStatusChangeEvent($order, $toStatus));
        #状态真的变了才会更新CRM
        if($order->user && $toStatus != $fromStatus){
            if($toStatus != Order::STATUS_OVERDUE){
                dispatch(new UpdateCustomerJob($order->user));
            }
            //状态变更修改优惠券使用情况,拒绝取消都返回优惠券
            if ( in_array($toStatus,[Order::STATUS_MANUAL_CANCEL,Order::STATUS_USER_CANCEL,Order::STATUS_SYSTEM_CANCEL,Order::STATUS_SYSTEM_REJECT,Order::STATUS_MANUAL_REJECT])
                && $receive = CouponReceive::model()->query()->where("order_id",$order->id)->first()) {
                $receive->order_id = null;
                $receive->use_time = '';
                $receive->save();
            }
            //完件结清发放邀请好友活动奖励
            //完件发放奖励
            if ( $order->user->invitedCode && $order->quality==0 && isset($params['signed_time']) && !empty($params['signed_time']) && $order->user->invitedCode ){
                ActivitiesRecordServer::server()->awardBonus(1,1,2,MerchantHelper::getMerchantId(),Carbon::now()->toDateTimeString(),$order->user->invitedCode->user_id);
            }
            //完件发放好友改造现金奖励
            if ( $order->quality==0 && isset($params['signed_time']) && !empty($params['signed_time']) ){
                $userInviteFriend = UserInviteFriend::whereTelephone($order->user->telephone)
                                    ->whereMerchantId($order->user->merchant_id)->first();
                if ( $userInviteFriend && $userInviteFriend->cash_back !=1 ){
                    $userInviteFriend->cash_back =1;
                    $userInviteFriend->save();
                }
            }
            //正常结清发放奖励
            if ( $order->user->invitedCode && $order->quality==0 && $toStatus==Order::STATUS_FINISH && $order->user->invitedCode  ){
                ActivitiesRecordServer::server()->awardBonus(1,1,4,MerchantHelper::getMerchantId(),Carbon::now()->toDateTimeString(),$order->user->invitedCode->user_id);
            }
            //客户结清当下就即刻触发复贷的短信
            if ( in_array($toStatus,Order::FINISH_STATUS) ){
                if ($toStatus == Order::STATUS_FINISH){
                    $order->user->bonus_count ++;
                    $order->user->save();
                }
                $appName = App::find($order->app_id)->app_name ?? '';
                dispatch(new SmsByCommonJob($order->user_id, SmsHelper::EVENT_OLD_USER_RECALL, ['appName' => $appName]));
                //黑名单失效范围,risk_blacklist表中，merchant_id=4，且apply_id的最后一位数值为 0、1、2的黑名单
                if ($order->merchant_id==4 && in_array(substr($order->id,-1,1),[0,1,2])){
                    \DB::table('risk_blacklist')->where('apply_id',$order->id)->update(['status' => RiskBlacklist::STATUS_DISABLE]);
                }
            }
        }

        return $order->setScenario(array_merge($params, [
            'status' => $toStatus,
            'updated_at' => $this->getDateTime(),
        ]))->save();
    }

    /**
     * 订单状态撤回最近一次
     * @param $orderId
     */
    public function rollbackStatus($orderId)
    {
        $lastOrderLog = OrderLog::whereOrderId($orderId)->orderByDesc('id')->first();
        if (!$lastOrderLog) {
            return false;
        }
        $fromStatus = $lastOrderLog->to_status;
        $toStatus = $lastOrderLog->from_status;
        return $this->changeStatus($orderId, $fromStatus, $toStatus);
    }

    /************************************************************************************************************************
     *  订单状态流程end
     ************************************************************************************************************************/

    /** 科目计算 begin */

    /**
     * 获取综合息费
     * 综合息费 = 本金 * 借款期限 * 日利息
     * @param $principal
     * @param $loanDays
     * @param $dailyRate
     * @return string
     */
    public function getInterestFee($principal, $loanDays, $dailyRate) {
        if ($loanDays != '') {
            $loanDays = str_replace([" "], '', $loanDays);
        }
        if ($loanDays != "") {
            $principal = str_replace([" "], '', $principal);
        }
        return MoneyHelper::round2point($principal * $dailyRate * $loanDays);
    }

    /**
     * 获取实际到账金额
     * 实际到账金额 = 本金 - (服务费+服务费gst) - 线下取款手续费(现金取款)
     * 注：统一调用 $order->getPaidAmount() 方法
     *
     * @param $order
     * @param $view
     * @return string
     */
    public function getPaidAmount(Order $order, $view = true)
    {
        if ($order->paid_amount > 0 && $view) {
            return $order->paid_amount;
        }

        $processingFeeAddGst = $this->getProcessingFeeAddGst($order);

        /** 线下取款需扣除取款手续费 */
        $withdrawalServiceCharge = $this->getWithdrawalServiceCharge($order);
        /** 放款金额向上取整，支付渠道要求 */
        return ceil($order->principal - $processingFeeAddGst - $withdrawalServiceCharge);
    }

    /**
     * 获取逾期金额
     * 逾期金额 = 本金 * 逾期天数 * 逾期日利率
     * @param Order $order
     * @param bool $cutOffBad
     * @return float|int|string
     */
    public function getOverdueFee(Order $order, $repaymentPlan = '')
    {
        $fee = 0;
        if ($repaymentPlan == '') {
            $isInFirst = $order->isInFirstRepaymentPlan();
            $isOnlyLast = $order->onlyLastRepaymentPlan();
            foreach ($order->repaymentPlans as $repaymentPlan) {
                # 已放款第二期未到期，只取第一期
                if ($isInFirst && $repaymentPlan->installment_num != 1) {
                    continue;
                }
                # 第一期已还第二期未还，只取第二期
                if ($isOnlyLast && $repaymentPlan->installment_num != 2) {
                    continue;
                }
                $fee += $this->getOverdueFee($order, $repaymentPlan);
            }
            return $fee;
        }

        $overdueDays = $order->getOverdueDays($repaymentPlan);
        if ($overdueDays > 0) {
            if ($overdueDays > 120) {
                $overdueDays = 120;
            }
            return MoneyHelper::round2point($order->getPaidPrincipal($repaymentPlan) * $order->overdue_rate * $overdueDays);
        }

        return 0.00;
    }

    /**
     * 获取应还金额
     * 应还金额 = 获取本应还金额 - 已还金额
     * @param Order $order
     * @param string $repaymentPlan
     * @return string
     */
    public function getRepayAmount(Order $order, $repaymentPlan = '')
    {
        $amountDue = $order->amountDue($repaymentPlan);
        $partRepayAmount = $order->getPartRepayAmount($repaymentPlan);
        # 已完结，应还返回0
        if ($repaymentPlan && in_array($repaymentPlan->status, RepaymentPlan::FINISH_STATUS)) {
            //$partRepayAmount = 0;
            return 0;
        }
        $repayAmount = MoneyHelper::round2point($amountDue - $partRepayAmount);
        # 兼容实际还款额大于应还金额
        return $repayAmount < 0 ? 0 : $repayAmount;
    }

    /**
     * 获取本应还金额
     * 应还金额 = 本金 + 息费 + 逾期 - 减免
     * @param $principal
     * @param $overdueFee
     * @param $reductionFee
     * @return string
     */
    public function amountDue(Order $order, $repaymentPlan = '')
    {
        $interestFee = $order->interestFee($repaymentPlan);
        $penaltyFeeAddGst = $order->getPenaltyFeeAddGst($repaymentPlan);
        $reductionFee = $order->getReductionFee($repaymentPlan);
        $total = $order->getPaidPrincipal($repaymentPlan) + $interestFee;

        return MoneyHelper::round2point($total + $penaltyFeeAddGst - $reductionFee);
    }

    /**
     * 获取逾期天数
     * @param $loanDays
     * @param $paidTime
     * @param $repayTime
     * @return float
     */
    public function getOverdueDays($loanDays, $paidTime, $repayTime = null, $repaymentPlan = null)
    {
        $appointmentPaidTime = $this->getAppointmentPaidTime($paidTime, $loanDays);
        if($repaymentPlan){
            $appointmentPaidTime = $repaymentPlan->appointment_paid_time;
        }
        if ($repayTime && $repayTime <= $appointmentPaidTime) {
            $repayTime = null;
            //return 0;
        }
        $nowDate = is_null($repayTime) ? Carbon::now()->toDateTimeString() : Carbon::parse($repayTime);
        $overdueDays = $this->getDiffDays($nowDate, $appointmentPaidTime);
        return $overdueDays;
    }

    /**
     * 获取应还时间
     * @param $paidTime
     * @param $loanDays
     * @return string
     */
    public function getAppointmentPaidTime($paidTime, $loanDays)
    {
        $paidTime = $paidTime ? $paidTime : DateHelper::dateTime();
        return Carbon::parse($paidTime)->addDays($loanDays)->toDateTimeString();
    }

    /**
     * 借款相差天数专用方法
     * @param $date1
     * @param $date2
     * @return float
     */
    public function getDiffDays($date1, $date2)
    {
        $time1 = Carbon::parse($date1)->startOfDay()->timestamp;
        $time2 = Carbon::parse($date2)->startOfDay()->timestamp;
        return ceil(($time1 - $time2) / 86400);
    }

    /** 科目计算 end */

    /** 拉取订单方法 begin */

    /**
     * 获取待放款订单
     * @param $with
     * @return Order|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function waitPayOrders($with = [])
    {
        /** 放款顺序 默认优先复贷用户,申请时间升序排序 */
        return Order::query()->with($with)
            ->whereIn('status', Order::WAIT_PAY_STATUS)
            ->orderBy('quality', 'desc')
            ->orderBy('created_at');
    }

    /**
     * 获取可批量执行代扣订单 当天&逾期1~3天
     * @param array $with
     * @return Order[]|Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getDaikouRepayOrders($with = [])
    {
        /** 当天23:59:59 */
        $endDateTime = Carbon::now()->endOfDay()->toDateTimeString();
        /** 3天前 00:00:00 */
        $startDateTime = Carbon::now()->subDay(3)->startOfDay()->toDateTimeString();

        return Order::query()->with($with)
            ->whereIn('status', Order::WAIT_REPAYMENT_STATUS)
            ->whereHas('lastRepaymentPlan', function (Builder $query) use ($startDateTime, $endDateTime) {
                $query->whereBetween('appointment_paid_time', [$startDateTime, $endDateTime]);
            })->get();
    }

    /**
     * 获取准备逾期订单
     * @return Order|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function beOverdueOrders()
    {
        /** 当天零点时间 */
        $zeroOfDay = Carbon::now()->toDateString();
        $query = RepaymentPlan::query();
        return $query->where('repay_time', '=', null)
            ->where('appointment_paid_time', '<', (string)$zeroOfDay)
            ->whereHas('order', function ($query) {
                $query->whereIn('status', Order::WILL_BE_OVERDUE_STATUS);
            })->orderByDesc("id");
    }

    /**
     * 获取准备坏账订单
     * @return Order|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function beCollectionBadOrders()
    {
        $day = Config::getValueByKey(Config::KEY_COLLECTION_BAD_DAYS);
        $query = RepaymentPlan::query();
        # day需囊括当天数据
        return $query->where('appointment_paid_time', '<', DateHelper::subDays((int)$day - 1))
            ->whereHas('order', function ($query) {
                $query->whereIn('status', Order::BE_OVERDUE_STATUS);
            });
    }

    /**
     * 未签约>7天订单
     * @param int $days
     * @return Order|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function noSignAfterDayOrders($days = 7)
    {
        //nio 0902 未签约取消订单改为7天
//        return Order::query()->whereIn('status', [Order::STATUS_MANUAL_PASS, Order::STATUS_SYSTEM_PASS])->where("signed_time", "=", null)->where('pass_time', '<',
//            Carbon::now()->subDay($days)->toDateString());
        //roc,按菲律宾风控新流程更改创建完件的订单7天未签约取消订单2021-02-25
        return Order::query()->where('status', Order::STATUS_CREATE)->where("signed_time", "=", null)->where('updated_at', '<',
            Carbon::now()->subDay($days)->toDateString());
    }

    /**
     * 重新提交材料>5天订单
     * @return Order|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function noReplenishAfter5Days()
    {
        return Order::query()->whereStatus(Order::STATUS_REPLENISH)->where('updated_at', '<',
            Carbon::now()->subDay(5)->toDateString());
    }

    /**
     * 线下取款未取款超时
     * @param int $days
     * @return Order|Builder|\Illuminate\Database\Eloquent\Model
     */
    public function noWithdrawMoney($days = 7)
    {
        return Order::query()->whereStatus(Order::STATUS_PAYING)->wherePayType(BankCardPeso::PAYMENT_TYPE_CASH)->where('updated_at', '<', Carbon::now()->subDay($days)->toDateTimeString());
    }

    /**
     * 获取可尾期减免的订单
     *
     * @return RepaymentPlan|Builder|\Illuminate\Database\Eloquent\Model
     */
    public function canDeductionOrders()
    {
        $query = RepaymentPlan::query();
        return $query->whereIn('status', RepaymentPlan::UNFINISHED_STATUS)
            ->whereDoesntHave('overdueRepayment')
            ->where('appointment_paid_time', '<=', DateHelper::addDays(1))
            ->where('loan_days', '=', Config::VALUE_MAX_LOAN_DAY)
            ->whereHas('order', function ($query) {
                //$query->whereIn('status', [Order::STATUS_SYSTEM_PAID, Order::STATUS_MANUAL_PAID]);
                $query->whereHas('orderDetails', function (Builder $query) {
                    $query->where('key', OrderDetail::KEY_LAST_INSTALLMENT_FREE_ON)
                        ->where('value', OrderDetail::ON);
                });
            });
    }

    /**
     * 获取可尾期减免的订单 (第一期完结T+1天，第二期自动完结)
     *
     * @return RepaymentPlan|Builder|\Illuminate\Database\Eloquent\Model
     */
    public function repayOneDayCanDeductionOrders()
    {
        $query = RepaymentPlan::query()->from('repayment_plan')->select('repayment_plan.*');
        # 关联第一期
        $query->join('repayment_plan as rp1', function ($join) {
            return $join->on('rp1.order_id', '=', 'repayment_plan.order_id')
                ->where('rp1.id', '!=', 'repayment_plan.id')
                ->where('rp1.installment_num', 1);
        });
        # 取第一期完结，第二期进行中
        $query->where('repayment_plan.installment_num', 2)
            ->whereIn('repayment_plan.status', RepaymentPlan::UNFINISHED_STATUS)
            ->where('rp1.status', RepaymentPlan::STATUS_FINISH)
            ->whereNotNull('rp1.repay_time')
            ->where('rp1.repay_time', '<', date('Y-m-d'));
        return $query;
    }

    /** 拉取订单方法 end */

    public function getCountByStatus($status)
    {
        return Order::query()->where('status', $status)->count();
    }

    /**
     * 按渠道统计订单数
     * @param $channel
     * @return mixed
     */
    public function countOrdersByChannel($channel)
    {
        return Order::model()->whereHas('user', function ($query) use ($channel) {
            $query->where('channel_id', $channel->id);
        })->count();
    }

    /**
     * 按渠道统计出款成功订单数
     * @param $channel
     * @return mixed
     */
    public function countOrdersSuccessByChannel($channel)
    {
        return Order::model()->whereIn('status', Order::WAIT_REPAYMENT_STATUS)
            ->whereHas('user', function ($query) use ($channel) {
                $query->where('channel_id', $channel->id);
            })->count();
    }

    /**
     * 按渠道统计出款成功订单放款金额
     * @param $channel
     * @return mixed
     */
    public function countPaidAmountsSuccessByChannel($channel)
    {
        return Order::model()->whereIn('status', Order::WAIT_REPAYMENT_STATUS)
            ->whereHas('user', function ($query) use ($channel) {
                $query->where('channel_id', $channel->id);
            })->sum('paid_amount');
    }

    /**
     * 获取订单被拒剩余天数
     * @param Order $order
     * @return int
     */
    public function getRejectLastDays(Order $order)
    {
        if (!in_array($order->status, Order::APPROVAL_REJECT_STATUS)) {
            return 0;
        }
        //获取拒绝时间
        $rejectTime = $order->getRejectTime();
        //获取预设被拒天数
        $rejectedDays = OrderDetail::model()->getRejectDays($order);
        //计算逾期结束时间
        $lastDaysTime = Carbon::parse($rejectTime)->addDays($rejectedDays);
        $days = DateHelper::diffInDays(DateHelper::date(), $lastDaysTime, false);
        return $days > 0 ? $days : 0;
    }

    /**
     * 根据后台配置获取订单开始状态
     *
     * @param User $user
     * @return string
     */
    public function getOrderCreateStatus(User $user)
    {
        // 直接进入待机审
        //return Order::STATUS_WAIT_SYSTEM_APPROVE;
        // 预创建订单
        return Order::STATUS_CREATE;
    }

    /**
     * 根据后台配置获取订单机审通过后状态
     * @param Order $order
     * @return string
     */
    public function getOrderSystemPassStatus(Order $order)
    {
//        $userApproveConfig = ConfigServer::server()->getUserApproveConfig($order);
//        //审批配置存在人审，优先进入人审
//        if (in_array(Approve::PROCESS_FIRST_APPROVAL, $userApproveConfig)) {
//            return Order::STATUS_WAIT_MANUAL_APPROVE;
//        }
//        //审批配置存在电审，进入电审
//        if (in_array(Approve::PROCESS_CALL_APPROVAL, $userApproveConfig)) {
//            return Order::STATUS_WAIT_CALL_APPROVE;
//        }
        if ($order->manual_check == Order::MANUAL_CHECK_REQUIRE) {
            return Order::STATUS_WAIT_MANUAL_APPROVE;
        }
        if ($order->call_check == Order::CALL_CHECK_REQUIRE) {
            return Order::STATUS_WAIT_CALL_APPROVE;
        }
        # 如无配置，进入审核通过待签约
        return Order::STATUS_SYSTEM_PASS;
    }

    /**
     * GST 手续费
     */
    public function getGstProcessingFee(Order $order, $repaymentPlan = '')
    {
        $orderDetail = new OrderDetail();
        $gstProcessingRate = $orderDetail->getGstProcessingRate($order);
        return MoneyHelper::round2point($gstProcessingRate * $order->getProcessingFee($repaymentPlan));
    }

    /**
     * GST 逾期费
     */
    public function getGstPenaltyFee(Order $order, $repaymentPlan = '')
    {
        $fee = 0;
        if ($repaymentPlan == '') {
            $isInFirst = $order->isInFirstRepaymentPlan();
            $isOnlyLast = $order->onlyLastRepaymentPlan();
            foreach ($order->repaymentPlans as $repaymentPlan) {
                # 已放款第二期未到期，只取第一期
                if ($isInFirst && $repaymentPlan->installment_num != 1) {
                    continue;
                }
                # 第一期已还第二期未还，只取第二期
                if ($isOnlyLast && $repaymentPlan->installment_num != 2) {
                    continue;
                }
                $fee += $this->getGstPenaltyFee($order, $repaymentPlan);
            }
            return $fee;
        }

        $orderDetail = new OrderDetail();
        $gstprocessingRate = $orderDetail->getGstPenaltyRate($order);
        return MoneyHelper::round2point($gstprocessingRate * $this->getOverDueFee($order, $repaymentPlan));
    }

    /**
     * 获取取款手续费
     * @param Order $order
     * @return float
     * @throws \Exception
     */
    public function getWithdrawalServiceCharge($order)
    {
        $paymentType = $order->getPaymentType();
        return $paymentType == BankCardPeso::PAYMENT_TYPE_CASH ? MoneyHelper::round2point(Config::getWithdrawalServiceCharge()) : MoneyHelper::round2point(Config::getOnlineServiceCharge());
    }

    /**
     * 砍头费+砍头费GST
     * @param Order $order
     * @param string $repaymentPlan
     * @return float|int|string
     * @throws \Exception
     */
    public function getProcessingFeeAddGst(Order $order, $repaymentPlan = '')
    {
        return ceil($order->getProcessingFee($repaymentPlan) + $this->getGstProcessingFee($order, $repaymentPlan));
    }

    public function getPenaltyFeeAddGst(Order $order, $repaymentPlan = '')
    {
        return MoneyHelper::round2point($this->getOverDueFee($order, $repaymentPlan) + $this->getGstPenaltyFee($order, $repaymentPlan));
    }

    public function getAuthProcess(Order $order, User $user)
    {
        if ($user->getAadhaarCardKYCStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
            return Order::AUTH_PROCESS_EKYC;
        }
        if (!$aadhaarAuth = UserAuth::model()->getAuth($user->id, [UserAuth::TYPE_AADHAAR_CARD])) {
            return '';
        }
        if (boolval((new UserThirdData())->getAadhaarVerityCheck($user->id, $aadhaarAuth->time))) {
            return Order::AUTH_PROCESS_AADHAAR_VERIFY;
        }
        return Order::AUTH_PROCESS_AADHAAR_OCR;
    }

    /**
     * 判断能否执行放款
     * @param $order
     * @return bool|OrderServer
     */
    public function canExecRemit($order)
    {
        /** 非待放款状态 */
        if (!in_array($order->status, Order::WAIT_PAY_STATUS)) {
            return false;
        }

        // NBFC未上报
//        if (!in_array($order->nbfc_report_status, Order::NBFC_PASS)) {
//            return false;
//        }

        return true;
    }

    /**
     * 跳过人审
     * @param Order $order
     */
    public function skipManualApprove($order)
    {
        $order->manual_check = Order::MANUAL_CHECK_NO;
        $order->call_check = Order::CALL_CHECK_NO;
        return $order->save();
    }

    public function getCanDeductionOrders($date){
        $orderIds = RepayDetail::model()->where(\DB::raw("date(actual_paid_time)"), $date)->get()->pluck("order_id");
        return AdminOrder::model()->whereIn("status", AdminOrder::WAIT_REPAYMENT_STATUS)->whereIn("id", $orderIds)->get();
    }

    public function getAllUnrepayOrders(){
        return AdminOrder::model()->whereIn("status", AdminOrder::WAIT_REPAYMENT_STATUS)->orderByDesc("id")->limit(1000)->get();
    }
}
