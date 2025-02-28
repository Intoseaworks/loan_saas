<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/11
 * Time: 13:23
 */

namespace Common\Models\Order;


use Carbon\Carbon;
use Common\Models\Config\Config;
use Common\Models\Repay\RepayDetail;
use Common\Models\User\User;
use Common\Services\Order\OrderServer;
use Common\Traits\Model\StaticModel;
use Common\Utils\Data\DateHelper;
use Common\Utils\Data\MoneyHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Order\RepaymentPlan
 *
 * @property int $id
 * @property int|null $user_id 用户id
 * @property int|null $order_id 订单id
 * @property string|null $no 还款编号
 * @property int|null $status 还款状态
 * @property int|null $overdue_days 逾期天数
 * @property string|null $appointment_paid_time 应还款时间
 * @property string|null $repay_time 还款时间
 * @property float|null $actual_repay_amount 实际还款金额
 * @property string|null $repay_channel 还款渠道
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property float|null $part_repay_amount 部分还款金额
 * @property float|null $ost_prncp min(应还金额，应还本金)
 * @property-read \Common\Models\Order\Order $order
 * @property-read \Common\Models\User\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereActualRepayAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereAppointmentPaidTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereOverdueDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereRepayChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereRepayTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereUserId($value)
 * @mixin \Eloquent
 * @property float|null $repay_amount 实际还款金额
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereRepayAmount($value)
 * @property float|null $reduction_fee 减免金额
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereReductionFee($value)
 * @property string $reduction_valid_date 减免有效期
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereReductionValidDate($value)
 * @property float|null $principal 实还本金
 * @property float|null $interest_fee 实还综合费用
 * @property float|null $overdue_fee 实还罚息
 * @property float|null $gst_penalty GST逾期费
 * @property float|null $gst_processing GST手续费
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereInterestFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereOverdueFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan wherePrincipal($value)
 * @property int $merchant_id merchant_id
 * @property int $installment_num 当前期数，默认为1
 * @property float|null $repay_proportion 当期还款比例
 * @property int|null $repay_days 当期还款天数
 * @property int|null $loan_days 当期借款天数
 * @property int|null $allow_renewal 允许续期
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereGstPenalty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereGstProcessing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereInstallmentNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereLoanDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereRepayDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\RepaymentPlan whereRepayProportion($value)
 * @property int|null $can_part_repay 催收配置可部分还款
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereCanPartRepay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereOstPrncp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan wherePartRepayAmount($value)
 * @property string|null $interest_start_time 利息计算开始日期
 * @method static \Illuminate\Database\Eloquent\Builder|RepaymentPlan whereInterestStartTime($value)
 */
class RepaymentPlan extends Model
{
    use StaticModel;

    const STATUS_CREATE     = 1;
    const STATUS_FINISH     = 2;
    const STATUS_REDUCTION  = 3;
    const STATUS_PART_REPAY = 4;
    const STATUS_RENEWAL    = 5;

    const STATUS_ALIAS = [
        self::STATUS_CREATE     => '未还款',
        self::STATUS_FINISH     => '已还款',
        self::STATUS_REDUCTION  => '减免',
        self::STATUS_PART_REPAY => '部分还款',
        self::STATUS_RENEWAL    => '已展期',
    ];

    /** 还款计划未完结状态 */
    const UNFINISHED_STATUS = [
        self::STATUS_CREATE,
        self::STATUS_PART_REPAY,
        self::STATUS_RENEWAL,
    ];

    /** 还款计划完结状态 */
    const FINISH_STATUS = [
        self::STATUS_FINISH,
        self::STATUS_REDUCTION,
    ];

    /**
     * @var string
     */
    protected $table = 'repayment_plan';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [
        'appointment_paid_time',
        'interest_start_time',
        'status',
        'part_repay_amount'
    ];
    /**
     * @var array
     */
    protected $hidden = [];

    const SCENARIO_CREATE               = 'create';
    const SCENARIO_REPAY_FINISH         = 'repay_finish';
    const SCENARIO_UPDATE_DEDUCTION_FEE = 'update_deduction_fee';

    const CAN_PART_REPAY    = 1;
    const CANNOT_PART_REPAY = 0;

    //允许续期
    const ALLOW_RENEWAL_IS_YES = 1;

    protected $with = [
        // 关联续期信息
        //'lastRepaymentPlanRenewal',
    ];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    protected function safes()
    {
        return [
            self::SCENARIO_CREATE               => [
                'merchant_id',
                'user_id',
                'order_id',
                'no'     => RepaymentPlan::generateNo(),
                'status' => RepaymentPlan::STATUS_CREATE,
                'appointment_paid_time',
                'installment_num',
                'repay_proportion',
                'repay_days',
                'loan_days',
                'interest_start_time'
            ],
            self::SCENARIO_REPAY_FINISH         => [
                'status' => RepaymentPlan::STATUS_FINISH,
                'overdue_days',
                'repay_time',
                'repay_amount',
                'repay_channel',
            ],
            self::SCENARIO_UPDATE_DEDUCTION_FEE => [
                'reduction_fee',
                'reduction_valid_date',
            ],
        ];
    }

    public function textRules()
    {
        return [
            'function' => [
                'appointment_paid_time' => function ($model) {
                    return DateHelper::formatToDate($model->appointment_paid_time);
                }
            ],
        ];
    }

    /**
     * 添加还款计划
     * @param Order $order
     * @param int $installmentNum
     * @return bool|RepaymentPlan
     */
    public function add(Order $order, $orderInstallmentData = [])
    {
        $loanDays = array_get($orderInstallmentData, 'loan_days') ?: $order->loan_days;
        $data     = [
            'merchant_id'           => $order->merchant_id,
            'user_id'               => $order->user_id,
            'order_id'              => $order->id,
            'appointment_paid_time' => OrderServer::server()->getAppointmentPaidTime($order->paid_time, $loanDays),
            'interest_start_time'   => $order->paid_time,
            //分期
            'installment_num'       => array_get($orderInstallmentData, 'installment_num', 1),//当前期数
            'repay_proportion'      => array_get($orderInstallmentData, 'repay_proportion', 100),//还款比例
            'repay_days'            => array_get($orderInstallmentData, 'repay_days', $loanDays),//还款天数
            'loan_days'             => $loanDays,//借款天数
        ];
        return self::model(self::SCENARIO_CREATE)->saveModel($data);
    }

    /**
     * 完结还款计划
     * @param $repayTime
     * @param $repayAmount
     * @param string $repayChannel
     * @param $reductionFee
     * @return bool
     */
    public function repayFinish($repayTime, $repayAmount, $repayChannel = '')
    {
        $order = $this->order;

        if ($this->part_repay_amount) {
            $this->part_repay_amount = MoneyHelper::add($repayAmount, $this->part_repay_amount);
            $repayAmount             = $this->part_repay_amount;
        } elseif ($this->repay_amount) {
            $repayAmount = MoneyHelper::add($repayAmount, $this->repay_amount);
        }
        $this->repay_amount = $repayAmount;

        // 如果当前还款计划是以完结的，说明此次完结为重复完结，只记录还款金额，不再更改其他属性
        if ($this->status == self::STATUS_FINISH) {
            return $this->save();
        }

        $this->repay_time    = $repayTime;
        $this->repay_channel = $repayChannel;

        $this->interest_fee   = $order->interestFee($this);//实还综合费用
        $this->overdue_fee    = $order->overdueFee($this);//实还罚息
        $this->principal      = $order->getPaidPrincipal($this);//实还本金
        $this->gst_processing = OrderServer::server()->getGstProcessingFee($order, $this);//实还GST手续费
        $this->gst_penalty    = OrderServer::server()->getGstPenaltyFee($order, $this);//实还GST逾期费
        $this->overdue_days   = $order->getOverdueDays($this); // 存真实逾期天数
        $this->ost_prncp      = 0;
        // 状态修改放最后
        $this->status = RepaymentPlan::STATUS_FINISH;

        return $this->save();

        // 应还金额 = 本金 + 综合息费(interestFee) + 罚息(overdueFee) + 罚息GST + 减免
    }

    public function partRepayFinish($repayAmount)
    {
        $order = $this->order;

        if ($this->status == static::STATUS_CREATE) {
            $this->status = RepaymentPlan::STATUS_PART_REPAY;
        }
        $this->part_repay_amount = MoneyHelper::add((float)$this->part_repay_amount, $repayAmount);

        $this->ost_prncp = min($order->repayAmount($this), $order->getPaidPrincipal($this));

        return $this->save();
    }

    public function repayReduction($repayTime, $repayAmount)
    {
        $order                = $this->order;
        $this->repay_time     = $repayTime;
        $this->repay_amount   = $repayAmount;
        $this->overdue_days   = $order->getOverdueDays($this);
        $this->repay_channel  = null;
        $this->status         = RepaymentPlan::STATUS_REDUCTION;
        $this->reduction_fee  = $order->getReductionFee($this);//实际减免金额
        $this->interest_fee   = $order->interestFee($this);//实还综合费用
        $this->overdue_fee    = $order->overdueFee($this);//实还罚息
        $this->principal      = $order->getPaidPrincipal($this);//实还本金
        $this->gst_processing = OrderServer::server()->getGstProcessingFee($order, $this);//实还GST手续费
        $this->gst_penalty    = OrderServer::server()->getGstPenaltyFee($order, $this);//实还GST逾期费
        return $this->save();
    }

    /**
     * 清理减免(坏账)
     */
    public function clearReGGduction()
    {
        $this->reduction_fee        = 0;
        $this->reduction_valid_date = '';
        $this->save();
    }

    /**
     * 清空逾期天数&减免&更新应还时间(续期成功)
     * @param $appointmentPaidTime
     * $appointmentPaidTime
     */
    public function clearOverdueDaysAndReduction($appointmentPaidTime = null)
    {
        $this->reduction_fee        = 0;
        $this->reduction_valid_date = '';

        $this->overdue_days = 0;
        if ($appointmentPaidTime) {
            $this->appointment_paid_time = $appointmentPaidTime;
        }
        $this->save();
    }

    /**
     * 获取应还本金比例
     *
     * @param Order $order
     * @return mixed
     */
    public function getNeedRepayProportion(Order $order)
    {
        return self::where('order_id', $order->id)
            ->whereIn('status', RepaymentPlan::UNFINISHED_STATUS)
            ->where('appointment_paid_time', '<=', DateHelper::dateTime())
            ->sum('repay_proportion');
    }

    /**
     * 获取逾期还款计划集合，若没有逾期则取第一笔还款计划
     * @param Order $order
     * @return Collection|\Illuminate\Support\Collection
     */
    public static function getNeedRepayRepaymentPlans(Order $order)
    {
        /** @var Collection $needRepayRepaymentPlan */
        $needRepayRepaymentPlan = $order->needRepayRepaymentPlan;

        if ($needRepayRepaymentPlan->isEmpty()) {
            if ($order->firstProgressingRepaymentPlan) {
                return collect([$order->firstProgressingRepaymentPlan]);
            }
            return collect();
        }

        return $needRepayRepaymentPlan;
    }

    /**
     * 更新减免
     * @param RepaymentPlan $repaymentPlan
     * @param $data
     * @return bool|RepaymentPlan
     */
    public function updateDeductionFee(RepaymentPlan $repaymentPlan, $data)
    {
        return $repaymentPlan->setScenario(self::SCENARIO_UPDATE_DEDUCTION_FEE)->saveModel($data);
    }

    public function isFinish()
    {
        return in_array($this->status, [self::STATUS_FINISH]);
    }

    public function isPartRepay()
    {
        return isset($this->part_repay_amount);
    }

    /**
     * 还款计划正处于逾期中
     */
    public function inOverdue()
    {
        $todayTimestamp      = Carbon::now()->startOfDay()->timestamp;
        $appointmentPaidTime = strtotime($this->appointment_paid_time);
        if (
            in_array($this->status, self::UNFINISHED_STATUS) &&
            is_null($this->repay_time) &&
            $appointmentPaidTime &&
            $appointmentPaidTime < $todayTimestamp
        ) {
            return true;
        }
        return false;
    }

    public function user($class = User::class)
    {
        return $this->hasOne($class, 'id', 'user_id');
    }

    public function order($class = Order::class)
    {
        return $this->hasOne($class, 'id', 'order_id');
    }

    public function lastRepaymentPlanRenewal($class = RepaymentPlanRenewal::class)
    {
        return $this->hasOne($class, 'repayment_plan_id', 'id')
            ->where('status', RepaymentPlanRenewal::STATUS_SUCCESS)
            ->orderBy('id', 'desc');
    }

    public function lastRepayDetail($class = RepayDetail::class)
    {
        return $this->hasOne($class, 'repayment_plan_id', 'id')->where([
            'status' => RepayDetail::STATUS_IS_VALID
        ])->orderBy('id', 'desc');
    }

    public function repaymentPlanRenewal($class = RepaymentPlanRenewal::class)
    {
        return $this->hasMany($class, 'repayment_plan_id', 'id')
            ->where('status', RepaymentPlanRenewal::STATUS_SUCCESS);
    }

    public function overdueRepayment($class = RepaymentPlan::class)
    {
        return $this->hasOne($class, 'order_id', 'order_id')
            ->whereIn('status', RepaymentPlan::UNFINISHED_STATUS)
            ->where('loan_days', '!=', Config::VALUE_MAX_LOAN_DAY);
    }

    /**
     * 生成还款编号
     * @param $prefix
     * @return string
     */
    public static function generateNo($prefix = null)
    {
        $prefix = $prefix ?: (string)mt_rand(1, 99999);
        $no     = '88' . strtoupper(substr(uniqid($prefix), 0, 16));
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
     * 获取还款计划上一次应还日期
     * 当不存在续期or只有一条续期，取repayment_plan中的应还时间
     * 当续期>1，取倒数第二条续期记录对应的还款日期
     * @return mixed
     */
    public function getPreviousAppointmentPaidTime()
    {
        /** @var Collection $renewalModels */
        $renewalModels = $this->repaymentPlanRenewal;
        if ($renewalModels->isEmpty() || $renewalModels->count() == 1) {
            return $this->getOriginal('appointment_paid_time');
        }
        $renewalModels = $renewalModels->sortByDesc('id')->values();
        return $renewalModels->get(1)->extends_appointment_paid_time;
    }

    /**
     *
     * @param $userId
     * @param $repaymentPlanNo
     * @return static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getOneByUser($userId, $repaymentPlanNo)
    {
        $where = [
            'user_id' => $userId,
            'no'      => $repaymentPlanNo,
        ];
        return $this->newquery()->where($where)->first();
    }

    /**
     * 获取未还期数
     */
    public function getProgressingRepaymentPlanCount($orderId)
    {
        return self::where('order_id', $orderId)
            ->whereIn('status', RepaymentPlan::UNFINISHED_STATUS)
            ->count();
    }

}
