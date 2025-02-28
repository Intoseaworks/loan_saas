<?php

namespace Api\Models\Order;

use Common\Utils\Data\DateHelper;

/**
 * Api\Models\Order\RepaymentPlan
 *
 * @property int $id
 * @property int|null $user_id 用户id
 * @property int|null $order_id 订单id
 * @property string|null $no 还款编号
 * @property int|null $status 还款状态
 * @property int|null $overdue_days 逾期天数
 * @property string|null $appointment_paid_time 应还款时间
 * @property string|null $repay_time 还款时间
 * @property float|null $repay_amount 实际还款金额
 * @property string|null $repay_channel 还款渠道
 * @property float|null $reduction_fee 减免金额
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan whereAppointmentPaidTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan whereOverdueDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan whereReductionFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan whereRepayAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan whereRepayChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan whereRepayTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan whereUserId($value)
 * @mixin \Eloquent
 * @property string $reduction_valid_date 减免有效期
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan whereReductionValidDate($value)
 * @property float|null $principal 实还本金
 * @property float|null $interest_fee 实还综合费用
 * @property float|null $overdue_fee 实还罚息
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan whereInterestFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan whereOverdueFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Order\RepaymentPlan wherePrincipal($value)
 * @property int $merchant_id merchant_id
 * @property float|null $gst_processing GST手续费
 * @property float|null $gst_penalty GST逾期费
 * @property int $installment_num 当前期数，默认为1
 * @property float|null $repay_proportion 当期还款比例
 * @property int|null $repay_days 当期还款天数
 * @property int|null $loan_days 当期借款天数
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlan whereGstPenalty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlan whereGstProcessing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlan whereInstallmentNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlan whereLoanDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlan whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlan whereRepayDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlan whereRepayProportion($value)
 * @property string|null $part_repay_amount 部分还款金额
 * @property int|null $can_part_repay 催收配置可部分还款
 * @property string|null $ost_prncp min(应还金额，应还本金)即min(sum(应还本金，应还罚息，应还利息)-repay_amt，应还本金）
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereCanPartRepay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereOstPrncp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan wherePartRepayAmount($value)
 * @property string|null $interest_start_time 利息计算开始日期
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereInterestStartTime($value)
 */
class RepaymentPlan extends \Common\Models\Order\RepaymentPlan
{
    const SCENARIO_HOME = 'home';
    const SCENARIO_LIST = 'list';

    public function textRules()
    {
        return [
            'function' => [
                'no' => function () {
                    if (in_array($this->scenario, [self::SCENARIO_HOME, self::SCENARIO_LIST])) {
                        $this->receivable_amount = $this->order->repayAmount($this);
                        $this->appointment_paid_time = DateHelper::formatToDate($this->appointment_paid_time);
                        // 当还款计划未还时，需计算费用
                        if($this->status == RepaymentPlan::STATUS_CREATE){
                            $this->principal = strval($this->order->getPaidPrincipal($this));
                            $this->interest_fee = strval($this->order->interestFee($this));
                            $this->reduction_fee = strval($this->order->getReductionFee($this));
                            $this->overdue_fee = strval($this->order->getPenaltyFeeAddGst($this));
                        }
                        $this->overdue_days = $this->order->getOverdueDays($this);
                        unset($this->order);
                    }
                }
            ]
        ];
    }

}
