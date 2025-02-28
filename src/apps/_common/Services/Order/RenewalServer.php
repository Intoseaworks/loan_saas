<?php

namespace Common\Services\Order;

use Common\Events\Order\OrderAgreementEvent;
use Common\Models\Order\Order;
use Common\Models\Order\RepaymentPlanRenewal;
use Common\Services\BaseService;
use Common\Services\Collection\CollectionServer;
use Common\Services\Config\LoanMultipleConfigServer;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;
use Common\Utils\Data\DateHelper;
use Common\Utils\Lock\LockRedisHelper;

class RenewalServer extends BaseService
{
    /**
     * 获取订单续期信息
     * @param $order Order
     * @return array
     */
    public function getRenewalInfo($order)
    {
        $lastRepaymentPlan = $order->lastRepaymentPlan;
        $subject = CalcRepaymentSubjectServer::server($lastRepaymentPlan)->getSubject();
//        $data = [
//            'min_repay_amount' => $subject->renewalPaidAmount,
//            'renewal_charge' => $subject->renewalFee,
//            'overdue_fee' => $subject->overdueFee,
//            'overdue_days' => $subject->overdueDays
//        ];
//        dd($data);
        $issue = $order->repaymentPlanRenewal->count();
        $renewalPreInfo = [
            'renewal_days'                   => [RepaymentPlanRenewal::RENEWAL_DEFAULT_DAYS],
            'min_repay_amount'            => $subject->renewalPaidAmount,
            'appointment_paid_time'          => DateHelper::format($subject->appointmentPaidTime,'Y/m/d'),
            'valid_period'                   => time() > strtotime($lastRepaymentPlan->appointment_paid_time) ? date('Y/m/d') : date('Y/m/d', strtotime($lastRepaymentPlan->appointment_paid_time)),
            "renewal_charge" => $subject->renewalFee,
            "overdue_fee" => $subject->overdueFee,
            "overdue_days" => $subject->overdueDays,
            'extend_appointment_paid_amount' => $subject->repaymentPaidAmount,
            'issue' => ++$issue,
            'interest_fee' => $subject->interestFee

        ];
        //展期状态信息
        $repaymentPlanRenewal = RepaymentPlanRenewal::model()->where('order_id',$order->id)->orderByDesc('id')->first();
        if ( $repaymentPlanRenewal ){
            $repaymentPlanRenewal->status == RepaymentPlanRenewal::STATUS_CREATE ? $renewalPreInfo['status']= $repaymentPlanRenewal->status : $renewalPreInfo['status']= 0;
        }else{
            $renewalPreInfo['status'] = 0;
        }
        /** @var $data RepaymentPlan */
        unset($order->lastRepaymentPlan);
        unset($order->orderDetails);
        unset($order->user);
        $order->renewalPreInfo = $renewalPreInfo;

        return $order;


        // 获取订单创建时最原始的loan_days
//        $renewalDays = $order->getOriginal('loan_days');
        $renewalDays = RepaymentPlanRenewal::RENEWAL_DEFAULT_DAYS;
        $appointmentPaidTime = $order->getRenewalAppointmentPaidTime($renewalDays, true);
        $renewalRate = LoanMultipleConfigServer::server()->getLoanRenewalRate($order->user, $order->loan_days) * 100;
        $issue = $order->repaymentPlanRenewal->count();

        $hint = [
            'normal' => "借款本金*续期天数*续期费率({$renewalRate}%)",
            'overdue' => "逾期息费+借款本金*续期天数*续期费率({$renewalRate}%)",
        ];
        $lastRepaymentPlan = $order->lastRepaymentPlan;
        $info = [
            'renewal_fee' => $order->renewalFee($renewalDays),
            'renewal_days' => [$renewalDays],
            'receivable_amount' => $order->repayAmount(),
            'appointment_paid_time' => $appointmentPaidTime,
            'valid_period'             => time() > strtotime($lastRepaymentPlan->appointment_paid_time) ? date('Y-m-d') : date('Y-m-d', strtotime($lastRepaymentPlan->appointment_paid_time)),
            'issue' => ++$issue,
            'hint' => $hint,
            'detailed_fee_explain' => $order->renewalFee($renewalDays, null, true),
        ];
        //展期状态信息
        $repaymentPlanRenewal = RepaymentPlanRenewal::model()->where('order_id',$order->id)->orderByDesc('id')->first();
        if ( $repaymentPlanRenewal ){
            $repaymentPlanRenewal->status == 1 ? $info['status']= 1 : $info['status']= 2;
        }else{
            $info['status'] = 0;
        }
        return $info;
    }

    /**
     * 确认续期
     * @param $order
     * @return RenewalServer
     * @throws \Exception
     */
    public function confirmRenewal($order)
    {
        // 加锁
        if (
            !LockRedisHelper::helper()->orderRenewalConfirm($order->id) ||
            $this->hasProcessedRenewal($order)
        ) {
            return $this->outputError('有一笔支付进行中，请勿重复交易');
        }

        $renewalModel = $this->createRenewal($order);
        //支付
        $tradeLog = OrderPayServer::server()->renewalPay($renewalModel);
        if (!$tradeLog) {
            $renewalModel->toRenewalFailed();
            return $this->outputError('扣款失败，请重试');
        }

        return $this->outputSuccess('', $tradeLog);
    }

    /**
     * 判断订单是否有处理中的续期
     * @param $order
     * @return bool
     */
    public function hasProcessedRenewal($order)
    {
        $repaymentPlanId = $order->lastRepaymentPlan->id;
        $lastRepaymentPlanRenewal = RepaymentPlanRenewal::model()->lastByRepaymentPlanId($repaymentPlanId);

        // 存在lastRepaymentPlanRenewal且状态为 创建
        return $lastRepaymentPlanRenewal ? $lastRepaymentPlanRenewal->status == RepaymentPlanRenewal::STATUS_CREATE : false;
    }

    /**
     * @param $order Order
     * @return bool|RepaymentPlanRenewal
     */
    public function createRenewal($order)
    {
        $renewalInfo = $this->getRenewalInfo($order);
        $data = [
            'order_id' => $order->id,
            'repayment_plan_id' => $order->lastRepaymentPlan->id,
            'renewal_days' => $renewalInfo['renewal_days'],
            'issue' => $renewalInfo['issue'],
            'renewal_fee' => $renewalInfo['renewal_fee'],
            'renewal_interest' => $renewalInfo['detailed_fee_explain']['renewal_interest'],
            'rate' => LoanMultipleConfigServer::server()->getLoanRenewalRate($order->user, $order->loan_days),
            'overdue_days' => $order->getOverdueDays(),
            'overdue_interest' => $renewalInfo['detailed_fee_explain']['overdue_fee'],
            'extends_appointment_paid_time' => $renewalInfo['appointment_paid_time'],
            // 记录续期前应还时间
            'appointment_paid_time_log' => $order->lastRepaymentPlan->appointment_paid_time,
        ];
        return RepaymentPlanRenewal::model()->add($data);
    }

    public function overRenewalResultSuccess($orderId, $ids)
    {
        $order = Order::model()->getOne($orderId);
        // 获取未续期前逾期天数
        $oldOverdueDays = $order->getOverdueDays();
        // 流转状态为续期成功
        RepaymentPlanRenewal::model()->statusToSuccess($orderId, $ids);
        // 清空订单逾期时间&更新
        $appointmentPaidTime = optional($order->refresh()->lastRepaymentPlan->lastRepaymentPlanRenewal)->extends_appointment_paid_time;
        $order->lastRepaymentPlan->clearOverdueDaysAndReduction($appointmentPaidTime);
        // 处于逾期和坏账的订单，状态流转回正常
        if (in_array($order->status, [Order::STATUS_OVERDUE, Order::STATUS_COLLECTION_BAD])) {
            // 终止催收&添加催收记录
            CollectionServer::server()->renewalDispose($order->collection, $oldOverdueDays);
            // 状态流转回正常
            OrderServer::server()->revertStatus($order->id, [Order::STATUS_SYSTEM_PAID, Order::STATUS_MANUAL_PAID]);
        }
        // 更新借款协议&居间协议&续期协议
        event(new OrderAgreementEvent($order->id));
    }

    public function overRenewalResultFailed($orderId, $ids)
    {
        // 流转状态为续期失败
        RepaymentPlanRenewal::model()->statusToFailed($orderId, $ids);
    }
}
