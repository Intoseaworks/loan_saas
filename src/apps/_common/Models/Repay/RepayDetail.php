<?php

namespace Common\Models\Repay;

use Common\Models\Order\Order;
use Common\Models\Order\RepaymentPlan;
use Common\Models\Trade\TradeLog;
use Common\Models\User\User;
use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * 
 * User: zy
 * Date: 20-11-10
 * Time: 下午8:47
 *
 * @property int $id 还款明细-主键自增id
 * @property int $uid 用户id
 * @property int $order_id 订单
 * @property int $repayment_plan_id 还款计划id
 * @property int $trade_id 关联trade_id
 * @property string $certificate 凭证-目前使用transaction_no
 * @property string $principal 实还本金(计算剩余本金)
 * @property string $interest_fee 实收利息
 * @property string $overdue_fee 实收罚息
 * @property string $renewal_fee 实收展期费用
 * @property string $forfeit_penalty 实收滞纳金
 * @property string $principal_overflow 本金溢出:用户多还部分,用来记录零头
 * @property string $paid_amount 实际支付金额
 * @property int $repay_type 还款行为类型:续期,调账,冲销,还款,续期->冲销
 * @property int $status 状态:1-生效,2-调账(该笔记录生效-金额不计入还款中)
 * @property int $admin_id 操作者id
 * @property string $appointment_paid_time 从还款计划哪里获取
 * @property string $actual_paid_time 实际支付时间
 * @property int $overdue_days 逾期天数
 * @property string $no_repay_overdue_fee 未支付罚息-理论上应该只有冲销类型才会有未支付罚息
 * @property string|null $origin_data 原始的order,repayment_plan相关数据
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereActualPaidTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereAppointmentPaidTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereCertificate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereForfeitPenalty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereInterestFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereNoRepayOverdueFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereOriginData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereOverdueDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereOverdueFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail wherePrincipal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail wherePrincipalOverflow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereRenewalFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereRepayType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereRepaymentPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereTradeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayDetail whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RepayDetail extends Model
{
    use StaticModel;

    protected $table = 'repay_detail';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [
        'uid',
        'order_id',
        'repayment_plan_id',
        'certificate',
        'principal',
        'interest_fee',
        'overdue_fee',
        'renewal_fee',
        'forfeit_penalty',
        'principal_overflow',
        'paid_amount',
        'repay_type',
        'status',
        'admin_id',
        'origin_data',
        'appointment_paid_time',
        'actual_paid_time',
        'overdue_days',
        'no_repay_over_due_fee',
        'updated_at',
        'created_at'
    ];

    const SCENARIO_CREATE = 'create';

    /**
     * 定义状态
     */
    /** 有效 */
    const STATUS_IS_VALID = 1;
    /** 调账 - 该笔支付接不计入当前还款计划中 */
    const STATUS_IS_OFFSET = 2;
    /** 撤销 */
    const STATUS_IS_CANCEL = 3;
    const STATUS_ALIAS     = [
        self::STATUS_IS_VALID => '有效',
        self::STATUS_IS_OFFSET => '调账',
        self::STATUS_IS_CANCEL => '撤销',
    ];
    /**
     * 定义还款类型
     */
    /** 展期 */
    const REPAY_TYPE_RENEWAL = 1;
    /** 冲销(部分还款) */
    const REPAY_TYPE_PART_REPAY = 2;
    /** 还款 */
    const REPAY_TYPE_REPAY = 3;
    /** 展期转冲销 */
    const REPAY_TYPE_RENEWAL_TO_PART_REPAY = 4;
    /** 还款转冲销 */
    const REPAY_TYPE_REPAY_TO_PART_REPAY = 5;
    /** 优惠券 **/
    const REPAY_TYPE_COUPON = 6;
    /** 减免 **/
    const REPAY_TYPE_DEDUCTION = 7;

    protected function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'uid',
                'order_id',
                'trade_id',
                'repayment_plan_id',
                'certificate',
                'principal',
                'interest_fee',
                'overdue_fee',
                'renewal_fee',
                'forfeit_penalty',
                'principal_overflow',
                'paid_amount',
                'repay_type',
                'status',
                'admin_id',
                'origin_data',
                'appointment_paid_time',
                'actual_paid_time',
                'overdue_days',
                'no_repay_overdue_fee',
                'updated_at',
                'created_at'
            ],
        ];
    }

    public function add($data)
    {
        return self::model(self::SCENARIO_CREATE)->saveModel($data);
    }

    /**
     * 获取已还本金
     * @param $repaymentPlanId
     * @return mixed
     */
    public static function getRepayPrincipal($repaymentPlanId)
    {
        return self::model()->where([
            'repayment_plan_id' => $repaymentPlanId,
            'status' => RepayDetail::STATUS_IS_VALID
        ])->sum('principal');
    }

    /**
     * 获取最后一条还款记录
     * @param $repaymentPlanId
     * @return Model|null|object|static
     */
    public static function lastRepayDetail($repaymentPlanId)
    {
        return self::model()->where([
            'repayment_plan_id' => $repaymentPlanId,
            'status' => RepayDetail::STATUS_IS_VALID
        ])->orderByDesc('id')->first();
    }

    /**
     * 获取有效的还款明细
     * @param $repaymentPlanId
     * @return Model|null|object|static
     */
    public static function getValidRepay($repaymentPlanId)
    {
        return self::model()->where([
            'repayment_plan_id' => $repaymentPlanId,
            'status' => RepayDetail::STATUS_IS_VALID
        ])->get();
    }


    public function order($class = Order::class)
    {
        return $this->hasOne($class, 'id', 'order_id');
    }

    public function user($class = User::class)
    {
        return $this->hasOne($class, 'id', 'uid');
    }

    public function repaymentPlan($class = RepaymentPlan::class)
    {
        return $this->hasOne($class, 'id', 'repayment_plan_id');
    }

    public function trade($class = TradeLog::class)
    {
        return $this->hasOne($class, 'id', 'trade_id');
    }
}
