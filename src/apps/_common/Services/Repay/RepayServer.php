<?php

namespace Common\Services\Repay;

use Admin\Services\Repayment\ManualRepaymentServer;
use Admin\Services\Repayment\RepaymentPlanServer;
use Common\Models\Order\RepaymentPlan;
use Common\Models\Order\RepaymentPlanRenewal;
use Common\Models\Repay\RepayDetail;
use Common\Models\Trade\TradeLog;
use Common\Services\BaseService;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;
use Common\Utils\MerchantHelper;
use DB;

/**
 * Created by PhpStorm.
 * User: zy
 * Date: 20-11-10
 * Time: 下午10:37
 */
class RepayServer extends BaseService
{
    /**
     * @var RepaymentPlan
     */
    public $repaymentPlan;

    /**
     * @var TradeLog
     */
    public $trade;

    /**
     * @var RepaymentPlanRenewal
     */
    public $repaymentPlanRenewal;

    /**
     * RepayServer constructor.
     * @param $repaymentPlan
     * @param $trade
     */
    public function __construct($repaymentPlan = null, $trade = null)
    {
        $this->repaymentPlan = $repaymentPlan;
        $this->trade         = $trade;
    }

    /**
     * 完结支付
     */
//    public function completeRepay()
//    {
//        if ($this->repaymentPlanRenewal) {
//            return $this->_completeRenewal();
//        }
//
//        return $this->_completeRepaymentPlan();
//    }


    /**
     * 完结支付
     */
    public function completeRepay($isDeduction = false,$isCouponRepay = false)
    {
        DB::beginTransaction();

        try {
            $subject = CalcRepaymentSubjectServer::server($this->repaymentPlan, $this->trade->trade_result_time)->getSubject();

            //RepayDetail -- 当前明细
            $repayDetail = [
                'uid' => $this->repaymentPlan->user_id,
                'trade_id' => $this->trade->id,
                'order_id' => $this->repaymentPlan->order_id,
                'repayment_plan_id' => $this->repaymentPlan->id,
                'certificate' => $this->trade->transaction_no,
                'origin_data' => json_encode([
                    'repayment_plan' => $this->repaymentPlan,
                    'repayment_plan_renewal' => $this->repaymentPlanRenewal
                ]),
                'appointment_paid_time' => $this->repaymentPlan->appointment_paid_time,
                'actual_paid_time' => $this->trade->trade_result_time,
                'overdue_days' => $subject->overdueDays,
                'status' => RepayDetail::STATUS_IS_VALID,
                'paid_amount' => $this->trade->trade_amount
            ];

            // >= 还款金额 -走还款
            if ((bccomp($this->trade->trade_amount, $subject->repaymentPaidAmount, 2) >= 0)) {
                MerchantHelper::setMerchantId($this->trade->merchant_id);
                //计算RepayDetail 科目
                $actualAmount = $this->trade->trade_amount;
                foreach ([
                             'principal' => $subject->surplusPrincipal,
                             'forfeit_penalty' => $subject->forfeitPenalty,
                             'overdue_fee' => $subject->overdueFee,
                             'interest_fee' => $subject->interestFee,
                         ] as $subject => $amount) {
                    if (bccomp($actualAmount, $amount, 2) >= 0) {
                        $actualAmount          = bcsub($actualAmount, $amount, 2);
                        $repayDetail[$subject] = $amount;
                    } else {
                        $repayDetail[$subject] = $actualAmount;
                        $actualAmount          = 0;
                        break;
                    }
                }

                //多余部分填充本金 - 如果没有的话 就是 0
                $repayDetail['principal_overflow'] = $actualAmount;

                //有专门的还款逻辑 - 该处不需要处理
                $repayDetail['repay_type'] = RepayDetail::REPAY_TYPE_REPAY;
                $params                    = [
                    'repay_time' => $this->trade->trade_result_time,
                    'repay_channel' => $this->trade->trade_platform,
                    'repay_amount' => $this->trade->trade_amount
                ];
                $res                       = ManualRepaymentServer::server()->flowRepaySuccess($this->repaymentPlan->order, $params, [$this->repaymentPlan->id], true, $this->trade);
                // $res = ManualRepaymentServer::server()->repaySuccess($this->trade, $this->trade->trade_platform_no, $this->trade->trade_result_time, $this->trade->trade_amount);
                if (!$res) {
                    throw new \Exception('订单完结异常');
                }
                // 有展期计划 && 时间在展期计划有效期内
            } else if ($renewal = RepaymentPlanRenewal::getValidRenewalByDate($this->repaymentPlan->id, date('Y-m-d', strtotime($this->trade->trade_result_time)))) {
                //已还款展期金额需累加
//                $renewalPaidAmount = $this->trade->trade_amount + $renewal->paid_amount;
                //回退不累加
                $renewalPaidAmount = $this->trade->trade_amount;
                if (bccomp( $renewalPaidAmount , $subject->renewalPaidAmount, 2) >= 0) {

                    // 完结还款续期
                    $renewal->update([
                        'renewal_fee' => $subject->renewalFee,
                        'renewal_interest' => $subject->interestFee,
                        'forfeit_penalty' => $subject->forfeitPenalty,
                        'rate' => $subject->renewalRate,
                        'overdue_days' => $subject->overdueDays,
                        'overdue_interest' => $subject->overdueFee,
                        'extends_appointment_paid_time' => $subject->appointmentPaidTime,
                        'appointment_paid_time_log' => $this->repaymentPlan->appointment_paid_time,
                        'paid_amount' => $renewalPaidAmount,
                        'payable_renewal_amount' => $subject->renewalFee,
                        'status' => RepaymentPlanRenewal::STATUS_SUCCESS
                    ]);

                    // 延长对应的还款计划
                    $this->repaymentPlan->update([
                        'appointment_paid_time' => $subject->appointmentPaidTime,
                        'interest_start_time' => date('Y-m-d H:i:s', strtotime($subject->appointmentPaidTime . "- {$renewal->renewal_days} days")),
                        'status' => RepaymentPlan::STATUS_RENEWAL
                    ]);

                    //计算RepayDetail 科目
                    $actualAmount = $this->trade->trade_amount;
                    foreach ([
                                 'forfeit_penalty' => $subject->forfeitPenalty,
                                 'overdue_fee' => $subject->overdueFee,
                                 'interest_fee' => $subject->interestFee,
                                 'renewal_fee' => $subject->renewalFee,
                             ] as $subjectName => $amount) {
                        if (bccomp($actualAmount, $amount, 2) >= 0) {
                            $actualAmount              = bcsub($actualAmount, $amount, 2);
                            $repayDetail[$subjectName] = $amount;
                        } else {
                            $repayDetail[$subjectName] = $actualAmount;
                            $actualAmount              = 0;
                            break;
                        }
                    }

                    //多余部分填充本金 - 如果没有的话 就是 0
                    $repayDetail['principal']            = $actualAmount;
                    $repayDetail['repay_type']           = RepayDetail::REPAY_TYPE_RENEWAL;
                    dispatch(new \Common\Jobs\Order\RenewalStopCollectionJob($this->trade->master_related_id, $this->trade->merchant_id));
                    // $repayDetail['no_repay_overdue_fee'] = $subject->overdueFee;
                } else {
                    // 写入repayDetail -- 续期转冲销
                    $repayDetail['principal']            = $this->trade->trade_amount;
                    $repayDetail['repay_type']           = RepayDetail::REPAY_TYPE_RENEWAL_TO_PART_REPAY;
                    $repayDetail['no_repay_overdue_fee'] = $subject->overdueFee;

                    //记录支付金额 & 时间,未达展期最小应还状态展期失败
                    $renewal->update([
                        'appointment_paid_time' => $subject->appointmentPaidTime,
                        'paid_amount' => $renewalPaidAmount,
                        'status' => RepaymentPlanRenewal::STATUS_FAILED
                    ]);
                }
                dispatch(new \Common\Jobs\Order\OrderDeductionJob($this->trade->master_related_id, $this->trade->merchant_id));
            } else {
                // 写入repayDetail - 还款转冲销
                $repayDetail['principal']            = $this->trade->trade_amount;
                $repayDetail['repay_type']           = RepayDetail::REPAY_TYPE_REPAY_TO_PART_REPAY;
                $repayDetail['no_repay_overdue_fee'] = $subject->overdueFee;

                //还款计划改为部分还款
                $this->repaymentPlan->update([
                    'part_repay_amount' => $this->repaymentPlan->part_repay_amount + $this->trade->trade_amount,
                    'status' => RepaymentPlan::STATUS_PART_REPAY
                ]);
                dispatch(new \Common\Jobs\Order\OrderDeductionJob($this->trade->master_related_id, $this->trade->merchant_id));
                //todo 本金溢出部分
            }
            if($isDeduction){
                $repayDetail['repay_type']           = RepayDetail::REPAY_TYPE_DEDUCTION;
            }
            if($isCouponRepay){
                $repayDetail['repay_type']           = RepayDetail::REPAY_TYPE_COUPON;
            }
            $repay = RepayDetail::model(RepayDetail::SCENARIO_CREATE)->add($repayDetail);

            DB::commit();

            return $repay;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }
    }
}
