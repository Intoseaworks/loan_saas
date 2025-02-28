<?php

namespace Admin\Services\Repay;

use Common\Models\{Order\Order, Order\RepaymentPlan, Order\RepaymentPlanRenewal, Repay\RepayDetail};
use Common\Services\{BaseService, Config\LoanMultipleConfigServer};

/**
 * 模拟计算还原科目
 * Created by PhpStorm.
 * User: zy
 * Date: 20-11-3
 * Time: 下午2:48
 */
class MockRestoreCalcSubjectServer extends BaseService
{
    /**
     * 利息
     */
    public $interestFee;

    /**
     * 利息 - 日利率
     */
    public $interestRate;

    /**
     * 罚息
     */
    public $overdueFee;

    /**
     * 滞纳金
     */
    public $forfeitPenalty;

    /**
     * 展期费用
     */
    public $renewalFee;

    /**
     * 减免费用
     */
    public $reductionFee;

    /**
     * 续期日费率
     */
    public $renewalRate;

    /**
     * 逾期天数
     */
    public $overdueDays;

    /**
     * 应支付金额 续期金额
     */
    public $renewalPaidAmount;

    /**
     * 应支付金额 还款金额
     */
    public $repaymentPaidAmount;

    /**
     * 约定还款时间
     */
    public $appointmentPaidTime;

    /**
     * @var RepaymentPlan 还款计划
     */
    public $repaymentPlan;

    /**
     * @var Order 订单
     */
    public $order;

    /**
     * @var string 指定的时间 Y-m-d H:i:s
     */
    public $time;

    /**
     * 本金
     */
    public $principal;

    /**
     * 剩余本金
     */
    public $surplusPrincipal;

    public $repayData;

    /**
     * CalcRepaymentSubjectServer constructor.
     * 计算各个科目需要的基数
     * @param RepaymentPlan $repaymentPlan 还款计划
     * @param null $time 给定的时间 不给 默认为当前时间
     * @param null $repayData 给定的时间 不给 默认为当前时间
     */
    public function __construct($repaymentPlan, $time = null, $repayData)
    {
        $this->repaymentPlan = $repaymentPlan;
        $this->order         = $repaymentPlan->order;
        $this->repayData     = $repayData;

        $this->time         = $time ?: date('Y-m-d H:i:s');
        $this->principal    = $this->repaymentPlan->order->principal;
        $this->interestRate = $this->order->daily_rate;

        // 计算剩余本金
        $this->_surplusPrincipal();

        // 逾期天数
        $this->_overdueDays();
    }

    /**
     * 剩余本金
     */
    private function _surplusPrincipal()
    {
        //根据还款记算剩余本金
        $repayPrincipal = $this->getRepayPrincipal();

        $surplusPrincipal = bcsub($this->principal, $repayPrincipal, 2);

        $this->surplusPrincipal = $surplusPrincipal < 0 ? 0 : $surplusPrincipal;

    }

    /**
     * 展期费率
     */
    private function _renewalRate()
    {
        $this->renewalRate = LoanMultipleConfigServer::server()->getLoanRenewalRate($this->order->user, $this->order->loan_days);
    }

    /**
     * 获取需求科目
     * @return $this
     */
    public function getSubject()
    {
        // 展期费率
        $this->_renewalRate();
        $this->_renewalFee();
        $this->_overdueFee();
        $this->_interestFee();
        $this->_forfeitPenalty();
        $this->_reductionFee();
        $this->_appointmentPaidTime();
        $this->_renewalPaidAmount();
        $this->_repaymentPaidAmount();

        return $this;
    }

    /**
     * 展期费用
     */
    private function _renewalFee()
    {
        $this->renewalFee = bcmul($this->surplusPrincipal, $this->renewalRate, 2);
    }

    /**
     * 逾期费用
     * 读取最后一条记录 - 如果是冲销，则应算上之前的未还罚息，
     * 逾期天数需要重新开始算-> 从最后一笔还款详情开始算， 因为剩余本金发生变化
     */
    private function _overdueFee()
    {
        $overdueFee = 0;
        $diffDays   = $this->overdueDays;

        $lastRepay = $this->lastRepayDetail();

        if ($lastRepay && in_array($lastRepay->repay_type, [RepayDetail::REPAY_TYPE_RENEWAL_TO_PART_REPAY, RepayDetail::REPAY_TYPE_REPAY_TO_PART_REPAY])) {
            $overdueFee = bcadd($overdueFee, $lastRepay->no_repay_overdue_fee, 2);
            $diffDays   = $this->_diffDays($lastRepay->actual_paid_time, $this->time);
        }

        if ($diffDays > 0) {

            // todo 逾期费率 - 需做成配置
            $overdueRateConfig = [
                '0.01' => range(1, 5),
                '0.02' => range(6, 10),
                '0.03' => range(11, 16),
                '0.35' => range(17, 30)
            ];

            $overdueRate = [];
            array_walk($overdueRateConfig, function ($overdueRange, $rate) use (& $overdueRate) {
                foreach ($overdueRange as $overdueDayFlag) {

                    $overdueRate[$overdueDayFlag] = $rate;
                }
            });

            foreach (range(1, $this->overdueDays) as $days) {
                $rate       = $overdueRate[$days] ?? 0;
                $overdueFee = bcadd($overdueFee, bcmul($this->surplusPrincipal, $rate, 2), 2);
            }
        }

        $this->overdueFee = $overdueFee;
    }

    /**
     * 利息
     * 剩余本金 * 利息日利率  * 逾期天数
     */
    private function _interestFee()
    {
        $this->interestFee = bcmul($this->surplusPrincipal, bcmul($this->overdueDays, $this->interestRate, 2), 2);
    }

    /**
     * 滞纳金
     * 计费基数为合同金额，逾期当天收取
     */
    private function _forfeitPenalty()
    {
        $rate = LoanMultipleConfigServer::server()->getLoanForfeitPenaltyRate($this->order->user);
        $this->forfeitPenalty = bcmul($this->principal, $rate, 2);
    }

    /**
     * 减免
     */
    private function _reductionFee()
    {
        $this->reductionFee = $this->repaymentPlan->reduction_fee;
    }

    /**
     * 计算逾期天数
     */
    private function _overdueDays()
    {
        $startDate = strtotime(date("Y-m-d", strtotime($this->repaymentPlan->appointment_paid_time)));
        $endDate   = strtotime(date("Y-m-d", strtotime($this->time)));

        $diffDays = intval(bcdiv(bcsub($endDate, $startDate), 86400));

        $this->overdueDays = $diffDays >= 0 ? $diffDays : 0;
    }

    /**
     * 计算天数差
     * @var string $starDate Y-m-d H:i:s
     * @var string $endDate Y-m-d H:i:s
     * @return int
     */
    private function _diffDays($startDate, $endDate)
    {
        $startDate = strtotime(date("Y-m-d", strtotime($startDate)));
        $endDate   = strtotime(date("Y-m-d", strtotime($endDate)));

        $diffDays = intval(bcdiv(bcsub($endDate, $startDate), 86400));

        return $diffDays >= 0 ? $diffDays : 0;
    }

    /**
     * 展期约定还款时间
     */
    private function _appointmentPaidTime()
    {
        $days                      = bcadd($this->overdueDays, RepaymentPlanRenewal::RENEWAL_DEFAULT_DAYS, 0);
        $this->appointmentPaidTime = date('Y-m-d H:i:s', strtotime($this->repaymentPlan->appointment_paid_time . "{$days} days"));
    }

    /**
     * 应支付金额 - 展期
     */
    private function _renewalPaidAmount()
    {
        $fee = 0;

        foreach ([
                     $this->interestFee,
                     $this->overdueFee,
                     $this->forfeitPenalty,
                     $this->renewalFee
                 ] as $amount) {
            $fee = bcadd($fee, $amount, 2);
        }

        $this->renewalPaidAmount = $fee;
    }

    /**
     * 应支付金额 - 还款
     */
    private function _repaymentPaidAmount()
    {
        $fee = 0;

        foreach ([
                     $this->interestFee,
                     $this->overdueFee,
                     $this->surplusPrincipal,
                 ] as $amount) {
            $fee = bcadd($fee, $amount, 2);
        }

        $this->repaymentPaidAmount = $fee;
    }

    private function getRepayPrincipal()
    {
        return !empty($this->repayData) ? array_sum(array_column($this->repayData, 'principal')) : 0;
    }

    private function lastRepayDetail()
    {
//        $data = array_combine()
        //todo 数组排序
        return end($this->repayData);
    }

}
