<?php

namespace Common\Models\Order;

use Carbon\Carbon;
use Common\Models\Approve\Approve;
use Common\Models\Approve\ManualApproveLog;
use Common\Models\Collection\Collection;
use Common\Models\Collection\CollectionRecord;
use Common\Models\Config\Config;
use Common\Models\Merchant\App;
use Common\Models\Merchant\Merchant;
use Common\Models\Trade\TradeLog;
use Common\Models\User\User;
use Common\Models\User\UserInfo;
use Common\Services\Config\LoanMultipleConfigServer;
use Common\Services\Order\OrderServer;
use Common\Traits\Model\StaticModel;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\DateHelper;
use Common\Utils\Data\MoneyHelper;
use Illuminate\Database\Eloquent\Model;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;
use Illuminate\Support\Facades\Cache;

/**
 * Common\Models\Order\Order
 *
 * @property int $id 订单id
 * @property string|null $order_no 订单编号
 * @property int|null $user_id 用户id
 * @property float|null $principal 贷款金额
 * @property int|null $loan_days 贷款天数
 * @property int|null $status 订单状态
 * @property int|null $created_at 创建时间
 * @property int|null $updated_at 更新时间
 * @property string|null $app_client APP终端
 * @property string|null $app_version APP版本号
 * @property-read \Common\Models\User\User $user
 * @property-read \Common\Models\User\UserInfo $userInfo
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereAppClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereAppVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereLoanDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order wherePrincipal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereUserId($value)
 * @mixin \Eloquent
 * @property int|null $quality 新老用户 0新用户 1老用户
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereQuality($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Models\Order\OrderDetail[] $orderDetails
 * @property-read \Common\Models\Order\RepaymentPlan $lastRepaymentPlan
 * @property-read RepaymentPlan $firstProgressingRepaymentPlan
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Models\Order\RepaymentPlan[] $repaymentPlans
 * @property float $daily_rate 日利率
 * @property float $overdue_rate 逾期费率
 * @property float $commission_rate 借款手续费
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereDailyRate($value)
 * @property string|null $system_time 机审时间
 * @property string|null $manual_time 人审时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereManualTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereSystemTime($value)
 * @property string|null $paid_time 放款时间
 * @property float|null $paid_amount 实际放款金额
 * @property string|null $pay_channel 放款渠道
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order wherePaidTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order wherePayChannel($value)
 * @property string|null $signed_time 签约时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereSignedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereOverdueRate($value)
 * @property string|null $cancel_time 取消时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereCancelTime($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Models\Trade\TradeLog[] $remitTradeLogs
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Models\Trade\TradeLog[] $receiptsTradeLogs
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Models\Collection\Collection $collection
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Models\Order\RepaymentPlanRenewal[] $repaymentPlanRenewal
 * @property string|null $pass_time 审批通过时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order wherePassTime($value)
 * @property string|null $overdue_time 转逾期时间
 * @property string|null $bad_time 转坏账时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereBadTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereOverdueTime($value)
 * @property int|null $merchant_id merchant_id
 * @property int $app_id app_id
 * @property int $approve_push_status 审批推送状态 0 不需要推送 1 未推送 2 已推送
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereApprovePushStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereCallCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereManualCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereMerchantId($value)
 * @property string|null $reject_time 被拒时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order whereRejectTime($value)
 * @property string|null $apply_principal 申请贷款金额
 * @property int $manual_check 是否需要初审, 1需要, 2不需要
 * @property int $call_check 是否需要电审,1需要，2不需要
 * @property int|null $manual_result 人审通过结果
 * @property int|null $call_result 电审通过结果
 * @property string|null $auth_process 认证过程，所属分流
 * @property int|null $nbfc_report_status NBFC上报状态 0:无需上报 1:未上报 2:已上报
 * @property string|null $flag 特殊订单标记
 * @property string|null $reference_no 还款码
 * @property-read Merchant|null $merchant
 * @method static \Illuminate\Database\Eloquent\Builder|Order orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereApplyPrincipal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereAuthProcess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCallResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereManualResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereNbfcReportStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereReferenceNo($value)
 * @property string|null $withdraw_no 取款码
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereWithdrawNo($value)
 * @property string|null $service_charge 砍头费
 * @property string|null $withdrawal_service_charge 线下取款手续费
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereServiceCharge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereWithdrawalServiceCharge($value)
 * @property string|null $pay_type 取款方式 cash现金 bank银行转账 other Gcash电子钱包
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePayType($value)
 * @property string|null $confirm_pay_time 确认放款时间
 * @property string|null $refusal_code 拒贷码
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereConfirmPayTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereRefusalCode($value)
 */
class Order extends Model {

    use StaticModel;

    const MNG_FEE_PERDAY = 'mng_fee_perday';
    const OVERDUE_FEE_PERDAY = 'overdue_fee_perday';
    const OVERDUE_FEE = [
        "1-3" => [self::MNG_FEE_PERDAY => 50, self::OVERDUE_FEE_PERDAY => 50],
        "4-7" => [self::MNG_FEE_PERDAY => 50, self::OVERDUE_FEE_PERDAY => 100],
        "8-15" => [self::MNG_FEE_PERDAY => 50, self::OVERDUE_FEE_PERDAY => 200],
        "16-30" => [self::MNG_FEE_PERDAY => 50, self::OVERDUE_FEE_PERDAY => 200],
        "31-100000" => [self::MNG_FEE_PERDAY => 50, self::OVERDUE_FEE_PERDAY => 200],
    ];

    /** @var int 新用户 */
    const QUALITY_NEW = 0;

    /** @var int 复贷用户 */
    const QUALITY_OLD = 1;

    /** @var array status */
    const QUALITY = [
        self::QUALITY_NEW => '新用户',
        self::QUALITY_OLD => '复贷用户',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /** 状态：创建订单 */
    const STATUS_CREATE = 'create';

    /** 状态：已确认待放款 */
    const STATUS_SIGN = 'sign';

    /** 状态：待系统机审 */
    const STATUS_WAIT_SYSTEM_APPROVE = 'wait_system_approve';

    /** 状态：待人工审核 */
    const STATUS_WAIT_MANUAL_APPROVE = 'wait_manual_approve';

    /** 状态：机审中 */
    const STATUS_SYSTEM_APPROVING = 'system_approving';

    /** 状态：人工审核通过待确认放款 */
    const STATUS_MANUAL_PASS = 'manual_pass';

    /** 状态：系统审核通过待确认放款 */
    const STATUS_SYSTEM_PASS = 'system_pass';

    /** 状态：系统审核拒绝 */
    const STATUS_SYSTEM_REJECT = 'system_reject';

    /** 状态：人工审核拒绝 */
    const STATUS_MANUAL_REJECT = 'manual_reject';

    /** 状态：待补充资料 */
    const STATUS_REPLENISH = 'replenish';

    /** 状态：放款处理中 */
    const STATUS_PAYING = 'paying';

    /** 状态：系统出款成功待还款 */
    const STATUS_SYSTEM_PAID = 'system_paid';

    /** 状态：人工出款成功待还款 */
    const STATUS_MANUAL_PAID = 'manual_paid';

    /** 状态：系统出款失败 */
    const STATUS_SYSTEM_PAY_FAIL = 'system_pay_fail';

    /** 状态：人工出款失败 */
    const STATUS_MANUAL_PAY_FAIL = 'manual_pay_fail';

    /** 状态：人工取消借款 */
    const STATUS_MANUAL_CANCEL = 'manual_cancel';

    /** 状态：用户取消借款 */
    const STATUS_USER_CANCEL = 'user_cancel';

    /** 状态：系统取消借款 */
    const STATUS_SYSTEM_CANCEL = 'system_cancel';

    /** 状态：借款已逾期 */
    const STATUS_OVERDUE = 'overdue';

    /** 状态：还款处理中 */
    const STATUS_REPAYING = 'repaying';

    /** 状态：正常结清 */
    const STATUS_FINISH = 'finish';

    /** 状态：逾期结清 */
    const STATUS_OVERDUE_FINISH = 'overdue_finish';

    /** 状态：已坏账 */
    const STATUS_COLLECTION_BAD = 'collection_bad';

    /** 状态：待电审 */
    const STATUS_WAIT_CALL_APPROVE = 'wait_call_approve';

    /** 状态: 待电二审 */
    const STATUS_WAIT_TWICE_CALL_APPROVE = 'wait_twice_call_approve';

    /** @var string 分流认证流程 */
    const AUTH_PROCESS_EKYC = 'ekyc'; //走ekyc认证
    const AUTH_PROCESS_AADHAAR_VERIFY = 'aadhaar_verify'; //走aadhaarOcr+aadhaar卡号认证
    const AUTH_PROCESS_AADHAAR_OCR = 'aadhaar_ocr'; //走aadhaarOcr认证

    /** @var int 人审通过 */
    const MANUAL_RESULT_PASS = 1;

    /** @var int 电审通过 */
    const CALL_RESULT_PASS = 1;
    const SCENARIO_INDEX = 'index';
    const SCENARIO_LIST = 'list';

    /** approve_push_status 审批推送状态 不需要推送 */
    const PUSH_STATUS_NO_NEED = 0;

    /** approve_push_status 审批推送状态 未推送 */
    const PUSH_STATUS_WAITING = 1;

    /** approve_push_status 审批推送状态 已推送 */
    const PUSH_STATUS_DONE = 2;

    /** call_check 需要电审 */
    const CALL_CHECK_REQUIRE = 1;

    /** call_check 不需要电审 */
    const CALL_CHECK_NO = 2;

    /** manual_check 需要初审 */
    const MANUAL_CHECK_REQUIRE = 1;

    /** manual_check 不需要初审 */
    const MANUAL_CHECK_NO = 2;

    /** 状态 */
    const STATUS_ALIAS = [
        self::STATUS_CREATE => '待签约',
        self::STATUS_WAIT_SYSTEM_APPROVE => '待系统机审',
        self::STATUS_SYSTEM_APPROVING => '机审中',
        self::STATUS_WAIT_MANUAL_APPROVE => '待人工审核',
        self::STATUS_REPLENISH => '待补充资料',
        self::STATUS_WAIT_CALL_APPROVE => '待电审',
        self::STATUS_WAIT_TWICE_CALL_APPROVE => '待电二审',
        self::STATUS_SYSTEM_PASS => '机审通过待确认放款',
        self::STATUS_MANUAL_PASS => '人审通过待确认放款',
        self::STATUS_SIGN => '已签约待放款',
        self::STATUS_PAYING => '放款处理中',
        self::STATUS_SYSTEM_PAID => '系统出款成功待还款',
        self::STATUS_MANUAL_PAID => '人工出款成功待还款',
        self::STATUS_OVERDUE => '借款已逾期',
        self::STATUS_REPAYING => '还款处理中',
        self::STATUS_FINISH => '正常结清',
        self::STATUS_OVERDUE_FINISH => '逾期结清',
        self::STATUS_COLLECTION_BAD => '已坏账',
        self::STATUS_SYSTEM_REJECT => '系统审核拒绝',
        self::STATUS_MANUAL_REJECT => '人工审核拒绝',
        self::STATUS_SYSTEM_PAY_FAIL => '系统出款失败',
        self::STATUS_MANUAL_PAY_FAIL => '人工出款失败',
        self::STATUS_MANUAL_CANCEL => '人工取消借款',
        self::STATUS_SYSTEM_CANCEL => '系统取消借款',
        self::STATUS_USER_CANCEL => '用户取消借款',
    ];

    /**
     * 已完成订单状态 人工取消/用户取消/正常结清/逾期结清/机审拒绝/人工拒绝
     */
    const STATUS_COMPLETE = [
        self::STATUS_MANUAL_CANCEL,
        self::STATUS_USER_CANCEL,
        self::STATUS_SYSTEM_CANCEL,
        self::STATUS_FINISH,
        self::STATUS_OVERDUE_FINISH,
        self::STATUS_SYSTEM_REJECT,
        self::STATUS_MANUAL_REJECT,
    ];

    /**
     * 未完成订单
     */
    const STATUS_NOT_COMPLETE = [
        self::STATUS_REPLENISH,
        self::STATUS_PAYING,
        self::STATUS_SYSTEM_PAID,
        self::STATUS_MANUAL_PAID,
        self::STATUS_SYSTEM_PAY_FAIL,
        self::STATUS_MANUAL_PAY_FAIL,
        self::STATUS_OVERDUE,
        self::STATUS_REPAYING,
        self::STATUS_COLLECTION_BAD,
        self::STATUS_CREATE,
        self::STATUS_SIGN,
        self::STATUS_WAIT_SYSTEM_APPROVE,
        self::STATUS_WAIT_MANUAL_APPROVE,
        self::STATUS_SYSTEM_APPROVING,
        self::STATUS_MANUAL_PASS,
        self::STATUS_SYSTEM_PASS,
        self::STATUS_WAIT_CALL_APPROVE,
        self::STATUS_WAIT_TWICE_CALL_APPROVE,
    ];

    /**
     * 未完成且审批通过订单
     */
    const STATUS_APPROVE_PASS = [
        self::STATUS_PAYING,
        self::STATUS_SYSTEM_PAID,
        self::STATUS_MANUAL_PAID,
        self::STATUS_SYSTEM_PAY_FAIL,
        self::STATUS_MANUAL_PAY_FAIL,
        self::STATUS_OVERDUE,
        self::STATUS_REPAYING,
        self::STATUS_COLLECTION_BAD,
        self::STATUS_SIGN,
        self::STATUS_MANUAL_PASS,
        self::STATUS_SYSTEM_PASS,
    ];

    /**
     * 待放款订单状态 已签约
     */
    const WAIT_PAY_STATUS = [
        self::STATUS_SIGN
    ];

    /**
     * 待确认放款放款订单状态 已签约
     */
    const WAIT_CONFIRM_PAY_STATUS = [
        self::STATUS_SYSTEM_PASS,
        self::STATUS_MANUAL_PASS,
    ];

    /**
     * 放款失败状态 人工放款失败/系统放款失败
     */
    const PAY_FAIL_STATUS = [
        self::STATUS_MANUAL_PAY_FAIL,
        self::STATUS_SYSTEM_PAY_FAIL,
    ];

    /**
     * 待审批状态：待机审、待人审、待补充资料、待电审
     */
    const APPROVAL_PENDING_STATUS = [
        self::STATUS_WAIT_SYSTEM_APPROVE,
        self::STATUS_WAIT_MANUAL_APPROVE,
//        self::STATUS_REPLENISH,
        self::STATUS_WAIT_CALL_APPROVE,
        self::STATUS_WAIT_TWICE_CALL_APPROVE,
    ];
    
    const APPROVAL_PENDING_STATUS_NEW = [
        self::STATUS_WAIT_SYSTEM_APPROVE,
        self::STATUS_WAIT_MANUAL_APPROVE,
        self::STATUS_WAIT_CALL_APPROVE,
    ];

    /**
     * 被拒状态：人审被拒 机审被拒
     */
    const APPROVAL_REJECT_STATUS = [
        self::STATUS_SYSTEM_REJECT,
        self::STATUS_MANUAL_REJECT,
    ];

    /**
     * 允许用户取消 待签约
     */
    const CAN_USER_CANCEL = [
        Order::STATUS_MANUAL_PASS
    ];

    /**
     * 待签约状态
     */
    const WAIT_SIGN = [
        self::STATUS_MANUAL_PASS,
        self::STATUS_SYSTEM_PASS,
    ];

    /**
     * 待还款状态：待还款/已逾期/已坏账
     */
    const WAIT_REPAYMENT_STATUS = [
        self::STATUS_SYSTEM_PAID,
        self::STATUS_MANUAL_PAID,
        self::STATUS_OVERDUE,
        self::STATUS_COLLECTION_BAD,
    ];

    /**
     * 已结清状态 正常结清/逾期结清
     */
    const FINISH_STATUS = [
        self::STATUS_FINISH,
        self::STATUS_OVERDUE_FINISH,
    ];

    /**
     * 可逾期状态 人工放款/系统放款/还款中/逾期
     */
    const BE_OVERDUE_STATUS = [
        Order::STATUS_MANUAL_PAID,
        Order::STATUS_SYSTEM_PAID,
        Order::STATUS_REPAYING,
        Order::STATUS_OVERDUE
    ];
    
    /**
     * 可逾期状态 人工放款/系统放款/还款中/逾期
     */
    const WILL_BE_OVERDUE_STATUS = [
        Order::STATUS_MANUAL_PAID,
        Order::STATUS_SYSTEM_PAID,
        Order::STATUS_REPAYING
    ];

    /**
     * 已放款订单状态
     */
    const CONTRACT_STATUS = [
        self::STATUS_SYSTEM_PAID,
        self::STATUS_MANUAL_PAID,
        self::STATUS_OVERDUE,
        self::STATUS_REPAYING,
        self::STATUS_COLLECTION_BAD,
        self::STATUS_FINISH,
        self::STATUS_OVERDUE_FINISH,
    ];

    /**
     * 还款计划状态 待还款/还款中/已逾期/已坏账/正常结清/逾期结清
     */
    const REPAYMENT_PLAN_STATUS = [
        self::STATUS_SYSTEM_PAID,
        self::STATUS_MANUAL_PAID,
        self::STATUS_OVERDUE,
        self::STATUS_COLLECTION_BAD,
        self::STATUS_REPAYING,
        self::STATUS_FINISH,
        self::STATUS_OVERDUE_FINISH,
    ];

    /**
     * 允许根据 order_log 流转回之前状态的
     */
    const ALLOW_REVERT_STATUS = [
        self::STATUS_REPAYING,
        self::STATUS_OVERDUE, // 续期后流转回之前状态
        self::STATUS_COLLECTION_BAD, // 续期后流转回之前状态
    ];

    /**
     * 电审中
     */
    const APPROVAL_CALL_STATUS = [
        self::STATUS_WAIT_CALL_APPROVE,
        self::STATUS_WAIT_TWICE_CALL_APPROVE
    ];

    /** NBFC上报状态：无需上报 */
    const NBFC_REPORT_STATUS_NO_NEED = 0;

    /** NBFC上报状态：未上报 */
    const NBFC_REPORT_STATUS_NO = 1;

    /** NBFC上报状态：已上报 */
    const NBFC_REPORT_STATUS_PASS = 2;

    /** nbfc通过可放款的订单状态 */
    const NBFC_PASS = [
        self::NBFC_REPORT_STATUS_NO_NEED,
        self::NBFC_REPORT_STATUS_PASS,
    ];

    /**
     * @var string
     */
    protected $table = 'order';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $hidden = [];
    protected $with = [
        //'repaymentPlanRenewal',
        'lastRepaymentPlan',
        'orderDetails',
    ];
    
    protected $subjectCalc;

    protected static function boot() {
        parent::boot();

        static::setAppIdOrMerchantIdBootScope();
    }

    /**
     * 获取续期天数累计
     * @return mixed
     */
    public function getRenewalDays() {
        $repayDays = 0;
        if ($this->repaymentPlanRenewal->isNotEmpty()) {
            $repayDays = $this->repaymentPlanRenewal->sum('renewal_days');
        }
        return $repayDays;
    }

    /**
     * 获取订单未过期天数
     * 未过期天数 = 借款天数+续期天数+已结清逾期天数
     * @return int|mixed|null
     */
    public function getLoanUnexpiredDays() {
        $renewalDays = $this->getRenewalDays();
        $unexpiredDays = $this->loan_days + $renewalDays + $this->repaymentPlanRenewal->sum('overdue_days');
        return $unexpiredDays;
    }

    public function sortCustom() {
        return [
            //订单创建时间
            'created_at' => [
                'field' => 'created_at',
            ],
            //实际到账金额 实际放款金额
            'paid_amount' => [
                'field' => 'paid_amount',
            ],
            //实际还款金额
            'repay_amount' => [
                'related' => 'lastRepaymentPlan',
                'field' => 'repay_amount',
            ],
            //逾期天数
            'overdue_days' => [
                'related' => 'lastRepaymentPlan',
                'field' => 'overdue_days',
            ],
            //应还款日期
            'appointment_paid_time' => [
                'related' => 'lastRepaymentPlan',
                'field' => 'appointment_paid_time',
            ],
            //已还款日期
            'repay_time' => [
                'related' => 'lastRepaymentPlan',
                'field' => 'repay_time',
            ],
            //借款金额
            'principal' => [
                'field' => 'principal',
            ],
            //减免金额
            'reduction_fee' => [
                'related' => 'lastRepaymentPlan',
                'field' => 'reduction_fee',
            ],
        ];
    }

    public function user($class = User::class) {
        return $this->hasOne($class, 'id', 'user_id');
    }

    public function repaymentPlans($class = RepaymentPlan::class) {
        return $this->hasMany($class, 'order_id', 'id');
    }

    /**
     * 最新还款计划
     * 注意！：对应第一笔还款计划
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastRepaymentPlan($class = RepaymentPlan::class) {
        return $this->hasOne($class, 'order_id', 'id')->orderBy('id')->withDefault();
    }

    /**
     * 最近一笔未还的还款计划
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function firstProgressingRepaymentPlan($class = RepaymentPlan::class) {
        return $this->hasOne($class, 'order_id', 'id')->whereIn('status', RepaymentPlan::UNFINISHED_STATUS)->orderBy('id');
    }

    public function lastInstallmentRepaymentPlan($class = RepaymentPlan::class) {
        return $this->hasOne($class, 'order_id', 'id')->where('loan_days', Config::VALUE_MAX_LOAN_DAY);
    }

    /**
     * 获取应还未还的还款计划
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function needRepayRepaymentPlan($class = RepaymentPlan::class) {
        return $this->hasMany($class, "order_id", "id")
                        ->whereIn('status', RepaymentPlan::UNFINISHED_STATUS)
                        ->where('appointment_paid_time', '<=', DateHelper::dateTime())
                        ->orderBy("id");
    }

    /**
     * 按状态最后一笔订单
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lastOrderByStatus($class = Order::class) {
        /** 关联自己查询id为最大的订单 * */
        return $this->belongsTo($class, 'id')
                        ->whereRaw('`id`=(select max(`laravel_reserved_0`.`id`) from `order` where `order`.`status` = `laravel_reserved_0`.`status`)');
    }

    public function order($class = Order::class) {
        return $this->belongsTo($class, 'id')
                        ->whereRaw('`id`=(select max(`order`.`id`) from `order` where `order`.`user_id` = `laravel_reserved_0`.`user_id`)');
    }

    /**
     * 订单详情
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderDetails($class = OrderDetail::class) {
        return $this->hasMany($class, 'order_id', 'id');
    }

    /** 入款交易记录
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receiptsTradeLogs($class = TradeLog::class) {
        return $this->hasMany($class, 'master_related_id', 'id')->whereTradeType(TradeLog::TRADE_TYPE_RECEIPTS);
    }

    /** 出款交易记录
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function remitTradeLogs($class = TradeLog::class) {
        return $this->hasMany($class, 'master_related_id', 'id')->whereTradeType(TradeLog::TRADE_TYPE_REMIT);
    }

    /**
     * 最新一笔交易记录
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastTradeLog($class = TradeLog::class) {
        return $this->hasOne($class, 'master_related_id', 'id')
                        ->orderBy('id', 'desc');
    }

    /**
     * 合同协议
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contractAgreements($class = ContractAgreement::class) {
        return $this->hasMany($class, 'order_id', 'id')
                        ->where('status', ContractAgreement::STATUS_ACTIVE);
    }

    /**
     * 获取单个借款合同协议
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function contractAgreementCashnowLoan($class = ContractAgreement::class) {
        return $this->hasOne($class, 'order_id', 'id')
                        ->where('name', ContractAgreement::CASHNOW_LOAN_CONTRACT)
                        ->where('status', ContractAgreement::STATUS_ACTIVE)
                        ->orderByDesc('id');
    }

    /**
     * 审批记录
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function approve($class = Approve::class) {
        return $this->hasMany($class, 'order_id', 'id')
                        ->where('status', Approve::STATUS_NORMAL);
    }

    public function ManualApproveLog($class = ManualApproveLog::class) {
        return $this->hasMany($class, 'order_id', 'id')
                        ->orderBy('id', 'desc');
    }

    /**
     * 最后一条审批记录
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastApprove($class = ManualApproveLog::class) {
        return $this->hasOne($class, 'order_id', 'id')
                        ->orderBy('id', 'desc');
    }

    /**
     * 人工审批记录
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function manualApprove($class = Approve::class) {
        return $this->hasOne($class, 'order_id', 'id')
                        ->where([
                            'status' => Approve::STATUS_NORMAL,
                            'type' => Approve::TYPE_MANUAL,
        ]);
    }

    /**
     * 订单状态流转记录
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderLog($class = OrderLog::class) {
        return $this->hasMany($class, 'order_id', 'id')->orderBy('id', 'desc');
    }

    /**
     * 最后一条订单状态流转记录
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastOrderLog($class = OrderLog::class) {
        return $this->hasOne($class, 'order_id', 'id')
                        ->orderBy('id', 'desc');
    }

    /**
     * 放款成功
     *
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tradeLogRemitSuccess($class = TradeLog::class) {
        return $this->tradeLog($class)
                        ->where('business_type', TradeLog::BUSINESS_TYPE_MANUAL_REMIT)
                        ->where('trade_result', TradeLog::TRADE_RESULT_SUCCESS);
    }
    
    public function tradeLogRemiting($class = TradeLog::class) {
        return $this->tradeLog($class)
                        ->where('business_type', TradeLog::BUSINESS_TYPE_MANUAL_REMIT)
                        ->where('trade_result', TradeLog::TRADE_RESULT_NULL)->orderBy('id', 'desc')->first();
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tradeLogRepaySuccess($class = TradeLog::class) {
        return $this->tradeLog($class)
                        ->where('business_type', TradeLog::BUSINESS_TYPE_REPAY)
                        ->where('trade_result', TradeLog::TRADE_RESULT_SUCCESS)->orderBy('id', 'desc');
    }

    public function tradeLog($class = TradeLog::class) {
        return $this->hasOne($class, 'master_related_id', 'id');
    }

    /**
     * 催收表
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function collection($class = Collection::class) {
        return $this->hasOne($class, 'order_id', 'id');
    }

    /**
     * 催收表 进行中（逾期坏账）
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function collectionUnfinished($class = Collection::class) {
        return $this->hasOne($class, 'order_id', 'id')->whereIn('status', Collection::STATUS_COLLECTION_UNFINISHED);
    }

    /**
     * 关联续期表
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function repaymentPlanRenewal($class = RepaymentPlanRenewal::class) {
        return $this->hasMany($class, 'order_id', 'id')
                        ->where('status', RepaymentPlanRenewal::STATUS_SUCCESS);
    }

    /**
     * 根据id获取
     * @param $id
     * @return $this|Model|object|null
     */
    public function getOne($id) {
        return self::whereId($id)->first();
    }

    /**
     * 获取综合息费
     * 综合息费 = 本金 * 借款期限 * 日利息
     * @param RepaymentPlan $repaymentPlan
     * @return float|null
     * @throws \Exception
     */
    public function interestFee($repaymentPlan = '') {
        $fee = 0;
        if ($repaymentPlan == '' && $this->paid_time) {
            $isInFirst = $this->isInFirstRepaymentPlan();
            $isOnlyLast = $this->onlyLastRepaymentPlan();
            foreach ($this->repaymentPlans as $repaymentPlan) {
                # 已放款第二期未到期，只取第一期息费
                if ($isInFirst && $repaymentPlan->installment_num != 1) {
                    continue;
                }
                # 第一期已还第二期未还，只取第二期
                if ($isOnlyLast && $repaymentPlan->installment_num != 2) {
                    continue;
                }
                $fee += $this->interestFee($repaymentPlan);
            }
            return $fee;
        }

        $dailyRate = $this->daily_rate ?? LoanMultipleConfigServer::server()->getDailyRate($this->user);
        $loanDays = $this->loan_days;
        # 已放款根据实际借款天数计息
        if ($this->paid_time) {
            $loanDays = $repaymentPlan->loan_days;
            # 已还款取实际借款天数
            if ($repaymentPlan->repay_time) {
                $isRepayLoanDays = DateHelper::diffInDays($this->paid_time, $repaymentPlan->repay_time);
                $loanDays = $isRepayLoanDays < $repaymentPlan->loan_days ? $isRepayLoanDays : $repaymentPlan->loan_days;
            }
        }
        return OrderServer::server()->getInterestFee($this->getPaidPrincipal($repaymentPlan), $loanDays, $dailyRate);
    }

    /**
     * 获取当前应还本金
     */
    public function getPaidPrincipal($repaymentPlan = '') {
        $repayProportion = 1;
        if ($repaymentPlan) {
            return $this->principal * $repaymentPlan->repay_proportion / 100;
        }
        if ($orderInstallment = OrderDetail::model()->getInstallment($this)) {
            # 已有到期的还款计划
            if ($needRepayProportion = RepaymentPlan::model()->getNeedRepayProportion($this)) {
                $repayProportion = $needRepayProportion / 100;
            } # 所有还款计划未到期，取第一条
            elseif ($this->firstProgressingRepaymentPlan) {
                $repayProportion = $this->firstProgressingRepaymentPlan->repay_proportion / 100;
            }
            # 无进行中还款计划取1
            return $this->principal * $repayProportion;
        }
        return $this->principal * $repayProportion;
    }

    /**
     * 获取放款金额
     * @param bool $view
     * @return string
     */
    public function getPaidAmount($view = true) {
        return OrderServer::server()->getPaidAmount($this, $view);
    }

    public function getPenaltyFeeAddGst($repaymentPlan = '') {
        return OrderServer::server()->getPenaltyFeeAddGst($this, $repaymentPlan);
    }

    /**
     * 获取砍头费
     * @param string $repaymentPlan
     * @return float
     * @throws \Exception
     */
    public function getProcessingFee($repaymentPlan = '')
    {
        if ($this->service_charge > 0) {
            return MoneyHelper::round2point($this->service_charge);
        }
        $serviceChargeRate = LoanMultipleConfigServer::server()->getServiceChargeRate($this->user, $this->loan_days);
        /** CLM等级对应手续费费率百分比 */
        $serviceChargeDiscount = (OrderDetail::model()->getServiceChargeDiscount($this) ?? 0) / 100;
        return MoneyHelper::round2point($this->principal * $serviceChargeRate * (1 - $serviceChargeDiscount));
    }

    /**
     * 获取逾期费用
     * @param string $repaymentPlan
     * @return float|int
     */
    public function overdueFee($repaymentPlan = '') {
        $repaymentPlan = $repaymentPlan ? : $this->lastRepaymentPlan;
        if($repaymentPlan){
            $this->subjectCalc = $this->subjectCalc ? : CalcRepaymentSubjectServer::server($repaymentPlan)->getSubject();
            return $this->subjectCalc->overdueFee;
        }
        return OrderServer::server()->getOverdueFee($this, $repaymentPlan);
    }

    public function getManagementAndOverdueFee($overdueDays=false) {
        return [$this->overdueFee(), 0];
    }

    /**
     * 获取应还款金额
     * @return string
     */
    public function repayAmount($repaymentPlan = '') {
        # 写死订单结束返回0
        if(in_array($this->status, Order::FINISH_STATUS)){
            return 0;
        }
        $repaymentPlan = $repaymentPlan ? : $this->lastRepaymentPlan;
        if($repaymentPlan){
            $this->subjectCalc = $this->subjectCalc ? : CalcRepaymentSubjectServer::server($repaymentPlan)->getSubject();
            return $this->subjectCalc->repaymentPaidAmount;
        }
        return OrderServer::server()->getRepayAmount($this, $repaymentPlan);
    }

    /**
     * 获取本应还款金额
     * @param string $repaymentPlan
     * @return string
     */
    public function amountDue($repaymentPlan = '') {
        return OrderServer::server()->amountDue($this, $repaymentPlan);
    }

    /**
     * 获取已部分还款金额
     * @param string $repaymentPlan
     * @return int
     */
    public function getPartRepayAmount($repaymentPlan = '') {
        if ($repaymentPlan != '') {
            return $repaymentPlan->part_repay_amount;
        }
        # 第一期未还
        if (optional($this->firstProgressingRepaymentPlan)->installment_num == 1) {
            return $this->firstProgressingRepaymentPlan->part_repay_amount;
        }
        $isOnlyLast = $this->onlyLastRepaymentPlan();
        # 第一期已还第二期未还
        if ($isOnlyLast) {
            return 0;
        }
        return 0;
    }

    /**
     * 获取应还款本金
     * @return string
     */
    public function repayPrincipal() {
        /** 应还金额小于本金 实际还款本金=应还金额 */
        if ($this->repayAmount() < $this->principal) {
            return $this->repayAmount();
        }
        return $this->principal;
    }

    /**
     * 获取减免金额(可减免本金)
     * @param string $repayTime
     * @return float|null
     */
    public function getReductionFee($repaymentPlan = '') {
        $fee = 0;
        if ($repaymentPlan == '') {
            $isInFirst = $this->isInFirstRepaymentPlan();
            $isOnlyLast = $this->onlyLastRepaymentPlan();
            foreach ($this->repaymentPlans as $repaymentPlan) {
                # 已放款第二期未到期，只取第一期
                if ($isInFirst && $repaymentPlan->installment_num != 1) {
                    continue;
                }
                # 第一期已还第二期未还，只取第二期
                if ($isOnlyLast && $repaymentPlan->installment_num != 2) {
                    continue;
                }
                $fee += $this->getReductionFee($repaymentPlan);
            }
            return $fee;
        }

        if (($lastRepaymentPlan = $repaymentPlan) || $lastRepaymentPlan = $this->lastRepaymentPlan) {
            if ($lastRepaymentPlan->status == RepaymentPlan::STATUS_FINISH) {
                return $lastRepaymentPlan->reduction_fee;
            }
            if (list($startDate, $endDate) = ArrayHelper::jsonToArray($lastRepaymentPlan->reduction_valid_date)) {
                $repayTime = date('Y-m-d');
                if ($repaymentPlan->repay_time) {
                    $repayTime = $repaymentPlan->repay_time;
                }
                if (DateHelper::betweenInDays($startDate, $endDate, DateHelper::formatToDate($repayTime))) {
                    return $lastRepaymentPlan->reduction_fee;
                }
            }
        }
        return 0;
    }

    /**
     * @return array|mixed
     */
    public function getReductionVal($repaymentPlan = '') {
        if (!$repaymentPlan) {
            $repaymentPlan = $this->firstProgressingRepaymentPlan;
        }
        if (!$repaymentPlan) {
            return ['', '', 0];
        }
        if (!($reductionValidDateArr = json_decode($this->lastRepaymentPlan->reduction_valid_date, true))) {
            return ['', '', 0];
        }
        if (!strtotime($reductionValidDateArr[0]) || !strtotime($reductionValidDateArr[1])) {
            return ['', '', 0];
        }
        $reductionValidDateArr[2] = $this->lastRepaymentPlan->reduction_fee;
        return $reductionValidDateArr;
    }

    /**
     * 获取应还款时间
     * @param bool $formatToDate
     * @return string
     */
    public function getAppointmentPaidTime($formatToDate = false) {
        $appointmentPaidTime = optional($this->firstProgressingRepaymentPlan)->appointment_paid_time;

        # 已完结取第一期应还
        if (in_array($this->status, self::FINISH_STATUS)) {
            $appointmentPaidTime = optional($this->lastRepaymentPlan)->appointment_paid_time;
        }

        if (!$appointmentPaidTime) {
            $appointmentPaidTime = DateHelper::addDays($this->loan_days);
        }

        if ($formatToDate) {
            $appointmentPaidTime = $appointmentPaidTime ? DateHelper::formatToDate($appointmentPaidTime) : '---';
        }
        return $appointmentPaidTime;
    }

    /**
     * 获取实际还款时间
     * @return string|null
     */
    public function getActualRepayTime() {
        return optional($this->lastRepaymentPlan)->repay_time;
    }

    /**
     * @param string $repaymentPlan 还款计划
     * @param bool $cutOffBad 截止坏账时间
     * @return float
     */
    public function getOverdueDays($repaymentPlan = '', $cutOffBad = false) {
        if ($repaymentPlan == '') {
            $repaymentPlan = $this->firstProgressingRepaymentPlan ?? $this->lastRepaymentPlan;
        }
        $repayTime = null;
        $unexpiredDays = $this->loan_days;
        if ($repaymentPlan && !is_bool($repaymentPlan)) {
            $unexpiredDays = $repaymentPlan->loan_days ?? $unexpiredDays;
            if ($repaymentPlan->repay_time != 0 && $repaymentPlan->repay_time != null) {
                $repayTime = $repaymentPlan->repay_time;
            }
        }
        if ($cutOffBad && $this->status == self::STATUS_COLLECTION_BAD && !empty($this->bad_time)) {
            $repayTime = $this->bad_time;
        }
        if ($this->repaymentPlanRenewal->isNotEmpty()) {
            // 未过期天数 = 借款天数+续期天数+已结清逾期天数
            $unexpiredDays = $this->getLoanUnexpiredDays();
        }
        if($repaymentPlan){
            return OrderServer::server()->getOverdueDays($unexpiredDays, $this->paid_time, $repayTime, $repaymentPlan);
        }
        return OrderServer::server()->getOverdueDays($unexpiredDays, $this->paid_time, $repayTime);
    }

    /**
     * 获取续期应还时间
     * 应还时间 = 放款日期+合同借款期限+逾期天数+新增续期天数
     *         = 当前应还日期+逾期天数+续期天数
     * lastRepaymentPlan->appointment_paid_time 已经等于 放款日期+合同借款期限+历史续期天数
     * 见:\Common\Models\Order\RepaymentPlan->getAppointmentPaidTimeAttribute()
     *
     * @param int $renewalDays
     * @param bool $formatToDate
     * @return Carbon|string|null
     */
    public function getRenewalAppointmentPaidTime(int $renewalDays, $formatToDate = false) {
        $appointmentPaidTime = $this->lastRepaymentPlan->appointment_paid_time;
        $overdueDays = $this->getOverdueDays();
        $overdueDays = $overdueDays > 0 ? $overdueDays : 0;

        $appointmentPaidTime = Carbon::parse($appointmentPaidTime)->addDay((int) $overdueDays + $renewalDays);

        return $formatToDate ? $appointmentPaidTime->toDateString() : $appointmentPaidTime->toDateTimeString();
    }

    /**
     * 获取续期费用
     * 续期费用 = 逾期息费+续期息费 = 逾期息费+(借款本金*续期天数*续期费率(x%))
     * @param $renewalDays
     * @param null $renewalRate
     * @param bool $returnAll
     * @return array|string
     */
    public function renewalFee($renewalDays, $renewalRate = null, $returnAll = false) {
        $renewalInterest = $this->renewalInterest($renewalDays, $renewalRate);
        $overdueFee = $this->overdueFee();
        $renewalFee = bcadd($renewalInterest, $overdueFee, 2);

        if (!$returnAll) {
            return $renewalFee;
        }

        return [
            'renewal_interest' => $renewalInterest,
            'overdue_fee' => $overdueFee,
            'renewal_fee' => $renewalFee,
        ];
    }

    /**
     * 计算续期息费
     * 续期息费 = 借款本金*续期天数*续期费率(x%)
     * @param $renewalDays
     * @param null $renewalRate
     * @return string
     */
    public function renewalInterest($renewalDays, $renewalRate = null) {
        $renewalRate = $renewalRate ? $renewalRate : LoanMultipleConfigServer::server()->getLoanRenewalRate($this->user, $this->loan_days);
        return OrderServer::server()->getInterestFee($this->principal, $renewalDays, $renewalRate);
    }

    /**
     * 获取订单详情列表
     * @return \Illuminate\Support\Collection
     */
    public function getOrderDetails() {
        $key = "Order::getOrderDetails::OrderID::{$this->id}".date("Ymd");
        return Cache::remember($key, 10, function(){
            return $this->orderDetails->pluck('value', 'key')->toArray();
        });
    }

    /**
     * 生成订单单号
     * @param null $prefix
     * @return string
     */
    public static function generateOrderNo($prefix = null) {
        $prefix = $prefix ?: (string) mt_rand(1, 99999);
        $no = '88'.str_pad(\Common\Utils\MerchantHelper::getMerchantId(),2,'0', STR_PAD_LEFT) . strtoupper(substr(uniqid($prefix), 0, 16));
        if ((new Order())->getByOrderNo($no)) {
            return self::generateOrderNo($prefix);
        }
        return $no;
    }

    /**
     * 根据交易单号获取交易记录
     * @param $orderNo
     * @return mixed
     */
    public static function getByOrderNo($orderNo) {
        return self::whereOrderNo($orderNo)->first();
    }

    /**
     * 获取待机审订单
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getWaitSystemApprove() {
        return self::query()->where('status', self::STATUS_WAIT_SYSTEM_APPROVE)->get();
    }

    /**
     * 获取待人工审批订单
     * @param array $notInIds
     * @param $limit
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getWaitManualApprove(array $notInIds = [], $limit = -1) {
        $query = self::query()->where('status', self::STATUS_WAIT_MANUAL_APPROVE)
                ->limit($limit);
        $notInIds && $query->whereNotIn('id', $notInIds);

        return $query->get();
    }

    /**
     * @param $id
     * @param array $where
     * @return Order|\Illuminate\Database\Eloquent\Builder|Model|object|null
     */
    public static function getById($id, $where = []) {
        $query = self::query()->where($where)->where('id', $id);

        return $query->first();
    }

    /**
     * 拒绝中
     * @return bool
     */
    public function isRejected() {
        return OrderServer::server()->getRejectLastDays($this) > 0;
    }

    /**
     * 订单进行中
     * @return bool
     */
    public function isNotComplete() {
        return in_array($this->status, self::STATUS_NOT_COMPLETE);
    }

    /**
     * 订单进行中已过审
     *
     * @return bool
     */
    public function isApprovePass() {
        return in_array($this->status, self::STATUS_APPROVE_PASS);
    }

    /**
     * 订单是否已完结
     *
     * @return bool
     */
    public function isFinished() {
        return in_array($this->status, self::FINISH_STATUS);
    }

    /**
     * 获取最新审核时间
     * @return string
     */
    public function getRejectTime()
    {
        return $this->manual_time ?? $this->system_time;
    }

    /**
     * 获取取款方式
     * @return string|null
     */
    public function getPaymentType()
    {
        return $this->pay_type ?? (optional($this->user->bankCard)->payment_type ?? '');
    }
    
    public function getPaymentChannel(){
        return optional($this->user->bankCard)->channel;
    }

    public function needCollectionOrders()
    {
        $day = Config::getValueByKey(Config::KEY_COLLECTION_BAD_DAYS);
        $query = $this->newQuery();
        $query->whereDoesntHave('collectionUnfinished');
        $query->whereIn('status', Order::BE_OVERDUE_STATUS);
        $query->whereHas('repaymentPlans', function ($query) use ($day) {
            $query->whereBetween('appointment_paid_time', [DateHelper::subDays((int)$day - 1), DateHelper::date()])
                ->whereIn('status', RepaymentPlan::UNFINISHED_STATUS);
        });
        return $query->get();
    }

    public function needCollectionOrderByDatesAndLevel($startDate, $endDate, $level, $params = []) {
        /* 强制分配 */
        $compel = isset($params['compel']) ? $params['compel'] : false;
        $query = $this->newQuery();
        $query->whereIn('status', Order::BE_OVERDUE_STATUS);
        $query->whereHas('repaymentPlans', function ($query) use ($startDate, $endDate) {
            $query->where("appointment_paid_time", ">=", $startDate)
                    ->where("appointment_paid_time", "<", $endDate)
                    ->whereIn('status', RepaymentPlan::UNFINISHED_STATUS)
                    ->where('installment_num', 1);
        });
        if (!$compel) {
            $query->where(function ($query) use ($level) {
                $query->whereDoesntHave('collectionUnfinished');
                $query->orWhereHas('collectionUnfinished', function ($query) use ($level) {
                    $query->where('level', '!=', $level);
                });
            });
        }

        if (($language = array_get($params, 'language')) && $language != 'All') {
            $query->whereHas('userInfo', function ($query) use ($language) {
                $query->where('language', 'REGEXP', implode('|', $language));
            });
        }
        $query->orderByDesc('quality');
        return $query->get();
    }

    /**
     * 还款说明
     */
    public function repayMsg() {
        if ($this->status == self::STATUS_OVERDUE_FINISH) {
            return '逾期' . $this->getOverdueDays() . '天还款';
        }

        if ($this->status == self::STATUS_FINISH) {
            $repayDaysByRemit = DateHelper::diffInDays($this->paid_time, $this->getAppointmentPaidTime());
            if ($repayDaysByRemit == 0) {
                return '到期还款';
            }
            return '借款' . $repayDaysByRemit . '天还款';
        }
        return '';
    }

    /**
     *
     * # 判断是否已放款, 且第二期未到期
     * @return bool
     */
    public function isInFirstRepaymentPlan() {
        if ($firstProgressingRepaymentPlan = $this->firstProgressingRepaymentPlan) {
            if ($firstProgressingRepaymentPlan->installment_num == 1 && (DateHelper::diffInDays($this->paid_time, date('Y-m-d')) <= $firstProgressingRepaymentPlan->loan_days)
            ) {
                return true;
            }
        }
        return false;
    }

    public function onlyLastRepaymentPlan() {
        if ($firstProgressingRepaymentPlan = $this->firstProgressingRepaymentPlan) {
            if ($firstProgressingRepaymentPlan->installment_num == 2) {
                return true;
            }
        }
        return false;
    }

    /* -------------cashnow code------------------------- */

    public function texts() {
        return [
            self::SCENARIO_INDEX => [
            ],
            self::SCENARIO_LIST => [
                'id',
                'order_no',
                'principal',
                'loan_days',
                'created_at',
                'updated_at',
                'status',
                'app_client',
                'app_version',
                'quality',
                'paid_amount',
                'paid_time',
                'pay_channel',
                'overdue_rate',
                'signed_time',
                'renewal_fee_aggregate',
                'confirm_pay_time',
            ],
        ];
    }

    public function textRules() {
        return [
            'array' => [
            ],
            'function' => [
                /** 订单科目 */
                'order_no' => function ($order) {
                    /** @var $order Order */
                    $this->appointment_paid_time = $order->getAppointmentPaidTime(); //应还时间
                },
            ]
        ];
    }

    /**
     *
     * @suppress PhanUndeclaredProperty
     * @return array
     */
    public function safes() {
        return [
        ];
    }

    /**
     * 判断订单能否签约
     * @return bool
     */
    public function canSign() {
        return $this->status == self::STATUS_CREATE;
    }

    /**
     * 根据id & user_id获取
     * @param $id
     * @param $userId
     * @return static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getOneByUser($id, $userId) {
        return self::whereId($id)->where([
                    'user_id' => $userId,
                ])->first();
    }

    public function nbfcReportPass() {
        $this->nbfc_report_status = self::NBFC_REPORT_STATUS_PASS;
        return $this->save();
    }

    public function orderDigio($class = OrderSignDoc::class) {
        return $this->hasOne($class, 'order_id', 'id')->orderByDesc('id');
    }

    public function userInfo($class = UserInfo::class) {
        return $this->hasOne($class, 'user_id', 'user_id');
    }

    public function collectionRecords($class = CollectionRecord::class) {
        return $this->hasMany($class, 'order_id', 'id');
    }

    public function merchant() {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'id');
    }

    public function app($class = App::class) {
        return $this->belongsTo($class, 'app_id', 'id');
    }
    
    public function riskStrategyResult($class = \Common\Models\Risk\RiskStrategyResult::class){
        return $this->hasOne($class, "order_id", "id")->orderByDesc('id')->first();
    }
    
    public function hardware($class = \Common\Models\UserData\UserPhoneHardware::class){
        return $this->hasOne($class, "order_id", "id")->orderByDesc('id');
    }
    
    /**
     * 获取应还总金额
     * @param string $repaymentPlan
     * @return float|int
     */
    public function allPrincipal($repaymentPlan = '') {
        $repaymentPlan = $repaymentPlan ? : $this->lastRepaymentPlan;
        if($repaymentPlan){
            $this->subjectCalc = $this->subjectCalc ? : CalcRepaymentSubjectServer::server($repaymentPlan)->getSubject();
            return $this->subjectCalc->allPrincipal;
        }
        return $this->amountDue($repaymentPlan);
    }
}
