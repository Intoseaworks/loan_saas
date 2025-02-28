<?php

namespace Common\Models\Order;

use Common\Traits\Model\StaticModel;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Order\RepaymentPlanRenewal
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
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal whereAppointmentPaidTimeLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal whereExtendsAppointmentPaidTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal whereIssue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal whereOverdueDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal whereOverdueInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal whereRenewalDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal whereRenewalFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal whereRenewalInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal whereRepaymentPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlanRenewal whereMerchantId($value)
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
class RepaymentPlanRenewal extends Model
{
    use StaticModel;

    /** 状态：未展期 */
    const STATUS_CREATE = 1;
    /** 状态：已展期*/
    const STATUS_SUCCESS = 2;
    /** 状态：续期失败 */
    const STATUS_FAILED = 3;
    /** 状态 */
    const STATUS          = [
        self::STATUS_CREATE => '未展期',
        self::STATUS_SUCCESS => '已展期',
        self::STATUS_FAILED => '续期失败',
    ];
    const SCENARIO_CREATE = 'create';

    /** 默认续期天数 */
    const RENEWAL_DEFAULT_DAYS = 7;

    /**
     * @var string
     */
    protected $table = 'repayment_plan_renewal';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [
        'renewal_days',
        'valid_period',
        'renewal_fee',
        'renewal_interest',
        'rate',
        'overdue_days',
        'overdue_interest',
        'extends_appointment_paid_time',
        'appointment_paid_time_log',
        'status',
        'payable_renewal_amount',
        'paid_amount',
    ];
    /**
     * @var array
     */
    protected $hidden = [];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'uid',
                'order_id',
                'repayment_plan_id',
                'renewal_days',
                'issue',
                'renewal_fee',
                'renewal_interest',
                'rate',
                'overdue_days',
                'overdue_interest',
                'extends_appointment_paid_time',
                'appointment_paid_time_log',
                'status' => self::STATUS_CREATE,
                'valid_period',
                "admin_id" => \Common\Utils\LoginHelper::getAdminId()
            ],
        ];
    }

    public function textRules()
    {
        return [
            'array' => [
                'status' => self::STATUS,
            ]
        ];
    }

    public function sortCustom()
    {
        return [
        ];
    }

    /**
     * @param $params
     * @return bool|RepaymentPlanRenewal
     */
    public function add($params)
    {
        return self::model(self::SCENARIO_CREATE)->saveModel($params);
    }

    /**
     * 流转续期失败
     */
    public function toRenewalFailed()
    {
        $this->status = self::STATUS_FAILED;
        return $this->save();
    }

    /**
     * 流转为续期成功
     * @param $orderId
     * @param $ids
     * @return mixed
     */
    public function statusToSuccess($orderId, $ids)
    {
        $where  = [
            'order_id' => $orderId,
            'status' => self::STATUS_CREATE,
        ];
        $update = [
            'status' => self::STATUS_SUCCESS,
        ];
        return self::where($where)->whereIn('id', (array)$ids)->update($update);
    }

    /**
     * 流转为续期失败
     * @param $orderId
     * @param $ids
     * @return mixed
     */
    public function statusToFailed($orderId, $ids)
    {
        $where  = [
            'order_id' => $orderId,
            'status' => self::STATUS_CREATE,
        ];
        $update = [
            'status' => self::STATUS_FAILED,
        ];
        return self::where($where)->whereIn('id', (array)$ids)->update($update);
    }

    public function lastByRepaymentPlanId($repaymentPlanId)
    {
        $where = [
            'repayment_plan_id' => $repaymentPlanId,
        ];
        return self::query()->where($where)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function order($class = Order::class)
    {
        return $this->belongsTo($class, 'order_id', 'id');
    }

    public function repaymentPlan($class = RepaymentPlan::class)
    {
        return $this->belongsTo($class, 'repayment_plan_id', 'id');
    }

    /**
     * 获取当前有效的续期
     * @param $repaymentPlanId
     * @return Model|null|object|static
     */
    public function getValidRenewal($repaymentPlanId)
    {
        $where = [
            ['repayment_plan_id', '=', $repaymentPlanId],
            ['valid_period', '=', date('Y-m-d')],
            ['status', '=', self::STATUS_CREATE]
        ];

        return self::query()->where($where)->first();
    }

    /**
     * 获取当前有效的续期 -- 根据时间
     * @param $repaymentPlanId
     * @return Model|null|object|static
     */
    public static function getValidRenewalByDate($repaymentPlanId, $date)
    {
        $where = [
            ['repayment_plan_id', '=', $repaymentPlanId],
            ['status', '=', self::STATUS_CREATE],
            ['valid_period', '>=', $date]
        ];

        return self::query()->where($where)->first();
    }

}
