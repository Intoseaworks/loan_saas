<?php

namespace Admin\Services\Repayment;

use Admin\Services\Collection\CollectionServer;
use Api\Services\Order\OrderServer;
use Common\Events\Order\OrderFlowPushEvent;
use Common\Models\Common\Config;
use Common\Models\Order\Order;
use Common\Models\Order\OrderDetail;
use Common\Models\Order\RepaymentPlan;
use Common\Services\BaseService;
use Common\Services\NewClm\ClmServer;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\DateHelper;
use Illuminate\Support\Facades\DB;

class RepaymentPlanServer extends BaseService
{
    /**
     * 创建还款计划
     * @param Order $order
     *
     */
    public function create($order)
    {
        $order->refresh();
        RepaymentPlan::model()->where("order_id", $order->id)->delete();
        if ($orderInstallments = OrderDetail::model()->getInstallment($order)) {
            $orderInstallments = json_decode($orderInstallments, true);
            $installmentNum = 1;
            $loanDays = 0;
            foreach ($orderInstallments as $orderInstallment) {
                $repayDays = array_get($orderInstallment, 'repay_days');
                $loanDays += $repayDays;
                $orderInstallmentData = [
                    'installment_num' => $installmentNum,
                    'repay_proportion' => array_get($orderInstallment, 'repay_proportion'),
                    'repay_days' => $repayDays,
                    'loan_days' => $loanDays,
                ];
                RepaymentPlan::model()->add($order, $orderInstallmentData);
                $installmentNum++;
            }
        } else {
            RepaymentPlan::model()->add($order);
        }
    }

    public function updateDeductionFee($repaymentPlan, $deduction, $deductionStart, $deductionEnd)
    {
        $data = [
            'reduction_fee' => $deduction,
            'reduction_valid_date' => ArrayHelper::arrayToJson([$deductionStart, $deductionEnd]),
        ];
        return RepaymentPlan::model()->updateDeductionFee($repaymentPlan, $data);
    }

    /**
     * 还款计划减免
     *
     * @param Order $order
     * @return bool
     * @throws \Exception
     */
    public function reductionRepaySuccess(Order $order, $repaymentPlan = '')
    {
        /** @var RepaymentPlan $repaymentPlan */
        if ($repaymentPlan == '') {
            $repaymentPlan = $order->firstProgressingRepaymentPlan;
        }
        if ($repaymentPlan) {
            if ($repaymentPlan->loan_days != Config::VALUE_MAX_LOAN_DAY && $repaymentPlan->installment_num != 2) {
                return $this->outputException('尾期天数异常');
            }
            $repayDate = DateHelper::dateTime();
            DB::beginTransaction();
            if (//还款计划状态变更
                $repaymentPlan->repayReduction($repayDate, $order->repayAmount($repaymentPlan)) &&
                //结清状态修改
                OrderServer::server()->repayFinish($order, [
                    'overdue_days' => $order->lastRepaymentPlan->overdue_days
                ]) &&
                //逾期完结
                CollectionServer::server()->finish($order->id)
            ) {
                DB::commit();

                event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_REPAY_FINISH));
                // NBFC还款上报
//            event(new NbfcReportEvent($order->id, NbfcReportConfig::REPORT_NODE_REPAY));
            } else {
                DB::rollBack();
                return false;
            }
            return true;
        }

        if ($order->refresh()->isFinished()) {
            /** CLM模块更新额度等级 */
            # nio 解决已结清后再次用老还款码时触发
            //ClmServer::server()->adjustLevel($order);
        }

        return false;
    }

}
