<?php

namespace Api\Models\Order;

/**
 * Api\Models\Order\RepaymentPlanRenewal
 *
 * @property int $id 自增id
 * @property int $order_id 订单id
 * @property int $repayment_plan_id 还款计划表id
 * @property int $renewal_days 续期天数
 * @property int $issue 续期期数(次数)
 * @property float $renewal_fee 续期费用(续期时收取总费用) = 续期息费+当期逾期息费 = (借款本金*续期天数*续期费率(x%)) + 逾期息费
 * @property float $renewal_interest 续期息费 = 借款本金*续期天数*续期费率(x%)
 * @property float $rate 续期费率
 * @property int $overdue_days 逾期天数  续期时的逾期天数
 * @property float $overdue_interest 逾期息费
 * @property string $extends_appointment_paid_time 应还款时间 放款日期+合同借款期限+逾期天数+续期天数
 * @property string $appointment_paid_time_log 续期前应还款日期log记录
 * @property int $status 状态  1:创建  2:续期成功
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal whereAppointmentPaidTimeLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal whereExtendsAppointmentPaidTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal whereIssue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal whereOverdueDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal whereOverdueInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal whereRenewalDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal whereRenewalFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal whereRenewalInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal whereRepaymentPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\RepaymentPlanRenewal whereMerchantId($value)
 * @property int $uid
 * @property string $forfeit_penalty 滞纳金
 * @property string $paid_amount 实际支付金额
 * @property string|null $valid_period 有效期
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlanRenewal orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlanRenewal query()
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlanRenewal whereForfeitPenalty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlanRenewal wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlanRenewal whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlanRenewal whereValidPeriod($value)
 * @property string $payable_renewal_amount 申请展期最小应还金额
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlanRenewal wherePayableRenewalAmount($value)
 */
class RepaymentPlanRenewal extends \Common\Models\Order\RepaymentPlanRenewal
{
    public function order($class = Order::class)
    {
        return $this->belongsTo($class, 'order_id', 'id');
    }
}
