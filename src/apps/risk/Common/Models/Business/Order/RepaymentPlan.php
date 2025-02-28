<?php

namespace Risk\Common\Models\Business\Order;

use Risk\Common\Models\Business\BusinessBaseModel;
use Risk\Common\Models\Business\User\User;

/**
 * Risk\Common\Models\Business\Order\RepaymentPlan
 *
 * @property int $id
 * @property int $app_id merchant_id
 * @property int|null $user_id 用户id
 * @property int|null $order_id 订单id
 * @property string|null $no 还款编号
 * @property int|null $status 还款状态
 * @property int|null $overdue_days 逾期天数
 * @property string|null $appointment_paid_time 应还款时间
 * @property string|null $repay_time 还款时间
 * @property string|null $repay_amount 实际还款金额
 * @property string|null $repay_channel 还款渠道
 * @property string|null $reduction_fee 减免金额
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property string|null $reduction_valid_date 减免有效期
 * @property string|null $principal 实还本金
 * @property string|null $interest_fee 实还综合费用
 * @property string|null $overdue_fee 实还罚息
 * @property string|null $gst_processing GST手续费
 * @property string|null $gst_penalty GST逾期费
 * @property int $installment_num 当前期数，默认为1
 * @property string|null $repay_proportion 当期还款比例
 * @property int|null $repay_days 当期还款天数
 * @property int|null $loan_days 当期借款天数
 * @property string|null $part_repay_amount 部分还款金额
 * @property int|null $can_part_repay 催收配置可部分还款
 * @property string|null $ost_prncp min(应还金额，应还本金)
 * 即
 * min(sum(应还本金，应还罚息，应还利息)-repay_amt，应还本金）
 * @property string|null $sync_time
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereAppointmentPaidTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereCanPartRepay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereGstPenalty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereGstProcessing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereInstallmentNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereInterestFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereLoanDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereOstPrncp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereOverdueDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereOverdueFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan wherePartRepayAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan wherePrincipal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereReductionFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereReductionValidDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereRepayAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereRepayChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereRepayDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereRepayProportion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereRepayTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereUserId($value)
 * @mixin \Eloquent
 */
class RepaymentPlan extends BusinessBaseModel
{
    const STATUS_CREATE = 1;
    const STATUS_FINISH = 2;
    const STATUS_REDUCTION = 3;

    const STATUS_ALIAS = [
        self::STATUS_CREATE => '创建',
        self::STATUS_FINISH => '完成',
        self::STATUS_REDUCTION => '减免',
    ];

    /** 还款计划完结状态 */
    const FINISH_STATUS = [
        self::STATUS_FINISH,
        self::STATUS_REDUCTION,
    ];
    public static $validate = [
        'data' => 'array',
        'data.*.id' => 'required|numeric', // 记录唯一ID，注意同条还款计划ID必须一致
        'data.*.order_id' => 'required|numeric', // 订单ID
        'data.*.status' => 'required|numeric', // 还款计划状态。未完成:0  已完成:1
        'data.*.overdue_days' => 'required|integer', // 逾期天数 大于等于0的整数
        'data.*.appointment_paid_time' => 'required|date', // 应还款时间
        'data.*.created_at' => 'required|date', // 还款计划创建时间
        'data.*.updated_at' => 'required|date', // 还款计划最后修改时间
        'data.*.installment_num' => 'required|integer', // 还款计划期数。订单下唯一 1,2,3
        'data.*.repay_proportion' => 'required|numeric', // 当期比例。当前还款计划占订单的比例

        'data.*.repay_time' => 'nullable|date', // 实际还款时间
        'data.*.repay_amount' => 'nullable|numeric', // 实际还款金额
        'data.*.repay_channel' => 'nullable', // 还款渠道
        'data.*.reduction_fee' => 'nullable|numeric', // 减免金额
        'data.*.interest_fee' => 'nullable|numeric', // 综合费用
        'data.*.overdue_fee' => 'nullable|numeric', // 逾期费用
        'data.*.no' => 'string', // 还款计划编号
    ];
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'data_repayment_plan';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [
        'id',
        'app_id',
        'user_id',
        'order_id',
        'no',
        'status',
        'overdue_days',
        'appointment_paid_time',
        'repay_time',
        'repay_amount',
        'repay_channel',
        'reduction_fee',
        'created_at',
        'updated_at',
        'reduction_valid_date',
        'principal',
        'interest_fee',
        'overdue_fee',
        'gst_processing',
        'gst_penalty',
        'installment_num',
        'repay_proportion',
        'repay_days',
        'loan_days',
        'part_repay_amount',
        'can_part_repay',
        'ost_prncp',
    ];

    /**
     * 生成还款编号
     * @param $prefix
     * @return string
     */
    public static function generateNo($prefix = null)
    {
        $prefix = $prefix ?: (string)mt_rand(1, 99999);
        $no = '88' . strtoupper(substr(uniqid($prefix), 0, 16));
        if ((new RepaymentPlan())->getByNo($no)) {
            return self::generateNo($prefix);
        }
        return $no;
    }

    /**
     * 根据还款编号获取还款计划
     * @param $no
     * @return mixed
     */
    public static function getByNo($no)
    {
        return self::whereNo($no)->first();
    }

    /**
     * @param $userId
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getByUserId($userId)
    {
        return self::query()->where('user_id', $userId)->get();
    }

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function user($class = User::class)
    {
        return $this->hasOne($class, 'id', 'user_id');
    }

    public function order($class = Order::class)
    {
        return $this->hasOne($class, 'id', 'order_id');
    }
}
