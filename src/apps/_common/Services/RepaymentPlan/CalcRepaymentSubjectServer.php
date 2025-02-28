<?php

namespace Common\Services\RepaymentPlan;

use Common\Models\{
    Order\Order,
    Order\RepaymentPlan,
    Order\RepaymentPlanRenewal,
    Repay\RepayDetail
};
use Common\Services\{
    BaseService,
    Config\LoanMultipleConfigServer
};

/**
 * Created by PhpStorm.
 * User: zy
 * Date: 20-11-3
 * Time: 下午2:48
 */
class CalcRepaymentSubjectServer extends BaseService {

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
     * 本金 - 还款计划
     */
    public $principal;

    /**
     * 本金 - 订单本金
     */
    public $order_principal;

    /**
     * 剩余本金
     */
    public $surplusPrincipal;

    /**
     * 放款时间
     */
    public $loanTime;

    /**
     * 利息开始计算时间
     */
    public $interestStartTime;

    /**
     * 已还金额
     * @var type 
     */
    public $repayPrincipal;

    /**
     * 总应还 本金+息费+罚息+滞纳金
     * @var type 
     */
    public $allPrincipal;

    /**
     * 处理部分还款时的差额
     * @var type 
     */
    public $imbalance = 0;

    /**
     * 有效的还款明细
     */
    public $repayDetail = [];

    /**
     * CalcRepaymentSubjectServer constructor.
     * 计算各个科目需要的基数
     * @param RepaymentPlan $repaymentPlan 还款计划
     * @param null $time 给定的时间 不给 默认为当前时间
     * @param null $repayAllData
     */
    public function __construct($repaymentPlan, $time = null, $repayAllData = null) {
        $this->repaymentPlan = $repaymentPlan;
        $this->order = $repaymentPlan->order;

        $this->time = $time ?: date('Y-m-d H:i:s');
        $this->principal = bcmul($this->order->principal, $repaymentPlan->repay_proportion / 100, 2);
        $this->order_principal = $this->order->principal;
        $this->interestRate = $this->order->daily_rate;
        $this->loanTime = $this->order->paid_time;
        $this->interestStartTime = $this->repaymentPlan->interest_start_time ?: $this->order->paid_time;

        // 计算剩余本金
        $this->_surplusPrincipal();

        // 逾期天数
        $this->_overdueDays();

        //还款明细
        $this->repayDetail = $repayAllData ? $repayAllData : $this->_repayDetail();
    }

    /**
     * 获取还款明细
     */
    private function _repayDetail() {
        $this->repayDetail = RepayDetail::getValidRepay($this->repaymentPlan->id);
    }

    /**
     * 剩余本金
     */
    private function _surplusPrincipal() {
        //根据还款记算剩余本金
        $this->repayPrincipal = RepayDetail::getRepayPrincipal($this->repaymentPlan->id);

        $surplusPrincipal = bcsub($this->principal, $this->repayPrincipal, 2);

        if ($surplusPrincipal < 0) {
            $this->imbalance = abs($surplusPrincipal);
        }
        $this->surplusPrincipal = $surplusPrincipal < 0 ? 0 : $surplusPrincipal;
    }

    /**
     * 展期费率
     */
    private function _renewalRate() {
        $this->renewalRate = LoanMultipleConfigServer::server()->getLoanRenewalRate($this->order->user, $this->order->loan_days);
    }

    /**
     * 获取需求科目
     * @return $this
     */
    public function getSubject() {
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
        $this->_allPrinclpal(); # 计算总应还金额

        return $this;
    }

    /**
     * 展期费用
     */
    private function _renewalFee() {
        $this->renewalFee = bcmul($this->surplusPrincipal, $this->renewalRate, 2);
    }

    /**
     * 逾期费用
     * 读取最后一条记录 - 如果是冲销，则应算上之前的未还罚息，
     * 逾期天数需要重新开始算-> 从最后一笔还款详情开始算， 因为剩余本金发生变化
     */
    private function _overdueFee() {
        $overdueFee = 0;
        $diffDays = $this->overdueDays;

        $lastRepay = RepayDetail::lastRepayDetail($this->repaymentPlan->id);

        if ($lastRepay && in_array($lastRepay->repay_type, [RepayDetail::REPAY_TYPE_RENEWAL_TO_PART_REPAY, RepayDetail::REPAY_TYPE_REPAY_TO_PART_REPAY])) {
            $overdueFee = bcadd($overdueFee, $lastRepay->no_repay_overdue_fee, 2);

            $startTime = $lastRepay->actual_paid_time > $this->repaymentPlan->appointment_paid_time ? $lastRepay->actual_paid_time : $this->repaymentPlan->appointment_paid_time;

            $diffDays = $this->_diffDays($startTime, $this->time);
        }

        if ($diffDays > 0) {

            // todo 逾期费率 - 需做成配置

            $overdueRateConfig = LoanMultipleConfigServer::server()->getPenaltyDaysRate($this->order->user);
            if (!$overdueRateConfig) {
                $overdueRateConfig = [
                    '0.01' => range(1, 5),
                    '0.02' => range(6, 10),
                    '0.03' => range(11, 16),
                    '0.03' => range(17, 30)
                ];
            }

            $overdueRate = [];
            array_walk($overdueRateConfig, function ($overdueRange, $rate) use (& $overdueRate) {
                foreach ($overdueRange as $overdueDayFlag) {

                    $overdueRate[$overdueDayFlag] = $rate;
                }
            });

            foreach (range(1, $diffDays) as $days) {
                $rate = $overdueRate[$days] ?? 0;
                $overdueFee = bcadd($overdueFee, bcmul($this->surplusPrincipal, $rate, 2), 2);
            }
        }
        $this->overdueFee = $overdueFee;
    }

    /**
     * 利息 - 利息只算到约定还款日期
     * 剩余本金 * 利息日利率  * 天数(不含逾期)
     */
    private function _interestFee() {
        // $surplusPrincipal = $this->principal;
        // $endTime = $this->repaymentPlan->appointment_paid_time > date('Y-m-d H:i:s') ?
        //     $this->repaymentPlan->appointment_paid_time : date('Y-m-d H:i:s');
        //利息 - 利息只算到约定还款日期
        $endTime = $this->repaymentPlan->appointment_paid_time;
        $days = $this->_diffDays($this->interestStartTime, $endTime);

        // foreach ($this->repayDetail as $repayDetail) {
        //     if (strtotime($repayDetail['actual_paid_time']) <= strtotime($this->time)) {
        //         //支付时间 <= 指定时间才有效
        //         $surplusPrincipal = bcsub($surplusPrincipal, $repayDetail['principal'], 2);
        //     }
        // }
        //没有记录,表示本金未发生变化 还款计划本金 * 天数 * 利率
        $interestFee = bcmul($this->principal, bcmul($days, $this->interestRate, 8), 2);

        $this->interestFee = $interestFee;
    }

    /**
     * 滞纳金
     * 计费基数为合同金额，逾期当天收取
     */
    private function _forfeitPenalty() {
        list($days, $rate) = LoanMultipleConfigServer::server()->getLoanForfeitPenaltyRate($this->order->user);
        $overdueDays = $this->order->getOverdueDays($this->repaymentPlan);
        $this->forfeitPenalty = $overdueDays >= $days ? bcmul($this->order_principal, $rate, 2) : bcmul(0, 1, 2);
    }

    /**
     * 减免
     */
    private function _reductionFee() {
        $this->reductionFee = $this->repaymentPlan->reduction_fee;
    }

    /**
     * 计算逾期天数
     */
    private function _overdueDays() {
        $startDate = strtotime(date("Y-m-d", strtotime($this->repaymentPlan->appointment_paid_time)));
        $endDate = strtotime(date("Y-m-d", strtotime($this->time)));

        $diffDays = intval(bcdiv(bcsub($endDate, $startDate), 86400));

        $this->overdueDays = $diffDays >= 0 ? $diffDays : 0;
    }

    /**
     * 计算天数差
     * @var string $starDate Y-m-d H:i:s
     * @var string $endDate Y-m-d H:i:s
     * @return int
     */
    private function _diffDays($startDate, $endDate) {
        $startDate = strtotime(date("Y-m-d", strtotime($startDate)));
        $endDate = strtotime(date("Y-m-d", strtotime($endDate)));

        $diffDays = intval(bcdiv(bcsub($endDate, $startDate), 86400));

        return $diffDays >= 0 ? $diffDays : 0;
    }

    /**
     * 展期约定还款时间
     */
    private function _appointmentPaidTime() {
        $days = bcadd($this->overdueDays, RepaymentPlanRenewal::RENEWAL_DEFAULT_DAYS, 0);
        $this->appointmentPaidTime = date('Y-m-d H:i:s', strtotime($this->repaymentPlan->appointment_paid_time . "+{$days} days"));
    }

    /**
     * 应支付金额 - 展期
     */
    private function _renewalPaidAmount() {
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
    private function _repaymentPaidAmount() {
        $fee = 0;
        if ($this->repaymentPlan->status == RepaymentPlan::STATUS_FINISH) {
            $this->repaymentPaidAmount = bcmul(1, 0, 2);
        } else {

            foreach ([
        $this->interestFee,
        $this->overdueFee,
        $this->forfeitPenalty,
        $this->surplusPrincipal,
            ] as $amount) {
                $fee = bcadd($fee, $amount, 2);
            }

            $this->repaymentPaidAmount = ($fee - $this->imbalance) >= 0 ? $fee - $this->imbalance : 0;
        }
    }

    private function _allPrinclpal() {
        $fee = 0;
        foreach ([
    $this->interestFee,
    $this->overdueFee,
    $this->forfeitPenalty,
    $this->principal,
        ] as $amount) {
            $fee = bcadd($fee, $amount, 2);
        }

        $this->allPrincipal = $fee;
    }

}
