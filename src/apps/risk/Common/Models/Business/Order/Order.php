<?php

namespace Risk\Common\Models\Business\Order;

use Illuminate\Database\Eloquent\Model;
use Risk\Common\Models\Business\BusinessBaseModel;
use Risk\Common\Models\Business\Collection\CollectionRecord;
use Risk\Common\Models\Business\User\User;
use Risk\Common\Models\Business\User\UserInfo;

/**
 * Risk\Common\Models\Business\Order\Order
 *
 * @property int $id 订单id
 * @property int|null $app_id merchant_id
 * @property int|null $business_app_id app_id
 * @property string|null $order_no 订单编号
 * @property int $user_id 用户id
 * @property string|null $principal 贷款金额
 * @property int|null $loan_days 贷款天数
 * @property string|null $status 订单状态
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property string|null $app_client APP终端
 * @property string|null $app_version APP版本号
 * @property int|null $quality 新老用户 0新用户 1老用户
 * @property string $daily_rate 日利率
 * @property string $overdue_rate 逾期费率
 * @property string|null $signed_time 签约时间
 * @property string|null $system_time 机审结束时间
 * @property string|null $manual_time 人审结束时间
 * @property string|null $paid_time 放款时间
 * @property string|null $paid_amount 实际放款金额
 * @property string|null $pay_channel 放款渠道
 * @property string|null $cancel_time 取消时间
 * @property string|null $pass_time 审批通过时间
 * @property string|null $overdue_time 转逾期时间
 * @property string|null $bad_time 转坏账时间
 * @property int|null $approve_push_status 审批推送状态 0 不需要推送 1 未推送 2 已推送
 * @property int|null $manual_check 是否需要初审, 1需要, 2不需要
 * @property int|null $call_check 是否需要电审,1需要，2不需要
 * @property string|null $reject_time 被拒时间
 * @property int|null $manual_result 人审通过结果
 * @property int|null $call_result 电审通过结果
 * @property string|null $auth_process 认证过程，所属分流
 * @property int|null $nbfc_report_status NBFC上报状态 0:无需上报 1:未上报 2:已上报
 * @property string|null $flag 特殊订单标记
 * @property string|null $sync_time
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereAppClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereAppVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereApprovePushStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereAuthProcess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereBadTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereBusinessAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCallCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCallResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCancelTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDailyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereLoanDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereManualCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereManualResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereManualTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereNbfcReportStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOverdueRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOverdueTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaidTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePassTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePayChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePrincipal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereQuality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereRejectTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereSignedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereSystemTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUserId($value)
 * @property-read \Common\Models\User\User $user
 * @property-read \Common\Models\User\UserInfo $userInfo
 * @mixin \Eloquent
 */
class Order extends BusinessBaseModel
{
    /** @var int 新用户 */
    const QUALITY_NEW = 0;
    /** @var int 复贷用户 */
    const QUALITY_OLD = 1;
    /** @var array status */
    const QUALITY = [
        self::QUALITY_NEW => '新用户',
        self::QUALITY_OLD => '复贷用户',
    ];

    const SYSTEM_PRINCIPAL_DEFAULT = 5000;
    const SYSTEM_LOAN_DAYS_DEFAULT = 14;
    /** 状态：创建订单 */
    const STATUS_CREATE = 'create';
    /** 状态：已签约 */
    const STATUS_SIGN = 'sign';
    /** 状态：待系统机审 */
    const STATUS_WAIT_SYSTEM_APPROVE = 'wait_system_approve';
    /** 状态：待人工审核 */
    const STATUS_WAIT_MANUAL_APPROVE = 'wait_manual_approve';
    /** 状态：机审中 */
    const STATUS_SYSTEM_APPROVING = 'system_approving';
    /** 状态：人工审核通过待签约 */
    const STATUS_MANUAL_PASS = 'manual_pass';
    /** 状态：系统审核通过待签约 */
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
    const AUTH_PROCESS_EKYC = 'ekyc';
    const AUTH_PROCESS_AADHAAR_VERIFY = 'aadhaar_verify';//走ekyc认证
    const AUTH_PROCESS_AADHAAR_OCR = 'aadhaar_ocr';//走aadhaarOcr+aadhaar卡号认证
    /** @var int 人审通过 */
    const MANUAL_RESULT_PASS = 1;//走aadhaarOcr认证
    /** @var int 电审通过 */
    const CALL_RESULT_PASS = 1;
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
        self::STATUS_WAIT_SYSTEM_APPROVE => '待系统机审',
        self::STATUS_SYSTEM_APPROVING => '机审中',
        self::STATUS_WAIT_MANUAL_APPROVE => '待人工审核',
        self::STATUS_REPLENISH => '待补充资料',
        self::STATUS_WAIT_CALL_APPROVE => '待电审',
        self::STATUS_WAIT_TWICE_CALL_APPROVE => '待电二审',
        self::STATUS_SYSTEM_PASS => '系统审核通过待签约',
        self::STATUS_MANUAL_PASS => '人工审核通过待签约',
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
        //self::STATUS_SYSTEM_PASS,
        //self::STATUS_MANUAL_PASS,
        self::STATUS_SIGN
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
        self::STATUS_REPLENISH,
        self::STATUS_WAIT_CALL_APPROVE,
        self::STATUS_WAIT_TWICE_CALL_APPROVE,
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
    public static $validate = [
        'data' => 'required|array',
        'data.*.id' => 'required|numeric',   // 订单唯一ID
        'data.*.order_no' => 'required',   // 订单业务编号
        'data.*.principal' => 'required|numeric',   // 借款金额
        'data.*.loan_days' => 'required|numeric',   // 借款天数
        'data.*.status' => 'required|string',   // 状态  创建:create  待审批:wait_system_approve   审批中:system_approving   审批通过:system_pass  已签约:sign  放款中:paying  出款成功待还款:system_paid   出款失败:system_pay_fail   取消借款:manual_cancel   已逾期:overdue   正常结清:finish   逾期结清:overdue_finish   已坏账:collection_bad
        'data.*.created_at' => 'required|date_format:Y-m-d H:i:s', // 订单创建时间
        'data.*.quality' => 'required|numeric',   // 新老用户类型 新用户:0  复借用户:1
        'data.*.daily_rate' => 'required|numeric',   // 日利率 小数。如0.09% 需传 0.0009
        'data.*.overdue_rate' => 'required|numeric',   // 逾期日利率 小数。 如0.09% 需传 0.0009
        'data.*.app_client' => 'string',   // 订单创建客户端  android  ios  h5
        'data.*.updated_at' => 'nullable|date',   // 最后修改时间
        'data.*.signed_time' => 'nullable|date',   // 签约时间
        'data.*.system_time' => 'nullable|date',   // 审批时间
        'data.*.paid_time' => 'nullable|date',   // 放款时间
        'data.*.paid_amount' => 'nullable|numeric',   // 支付金额
        'data.*.pay_channel' => 'nullable|string',   // 支付渠道
        'data.*.cancel_time' => 'nullable|date',   // 取消时间
        'data.*.pass_time' => 'nullable|date',   // 审批通过时间
        'data.*.overdue_time' => 'nullable|date',   // 逾期时间
        'data.*.bad_time' => 'nullable|date',   // 坏账时间
        'data.*.reject_time' => 'nullable|date',   // 拒绝时间
        'data.*.app_version' => 'string',   // 版本号
    ];
    /**
     * @var bool
     */
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'data_order';
    protected $with = [
        //'repaymentPlanRenewal',
        'lastRepaymentPlan',
        'orderDetails',
    ];
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [
        'id',
        'app_id',
        'business_app_id',
        'order_no',
        'user_id',
        'principal',
        'loan_days',
        'status',
        'created_at',
        'updated_at',
        'app_client',
        'app_version',
        'quality',
        'daily_rate',
        'overdue_rate',
        'signed_time',
        'system_time',
        'manual_time',
        'paid_time',
        'paid_amount',
        'pay_channel',
        'cancel_time',
        'pass_time',
        'overdue_time',
        'bad_time',
        'approve_push_status',
        'manual_check',
        'call_check',
        'reject_time',
        'manual_result',
        'call_result',
        'auth_process',
        'nbfc_report_status',
        'flag',
    ];

    public static function getByIdAndAppId($orderId, $appId = null)
    {
        $query = self::query()->where('id', $orderId);

        if ($appId) {
            $query->where('app_id', $appId);
        }

        return $query->first();
    }

    public static function getByIdAndUserId($userId, $orderId)
    {
        $where = [
            'id' => $orderId,
            'user_id' => $userId,
        ];

        return Order::query()->where($where)->orderByDesc('id')->first();
    }

    protected static function boot()
    {
        parent::boot();

//        static::setAppIdOrMerchantIdBootScope();
        static::setMerchantIdBootScope();
    }

    /**
     * @suppress PhanUndeclaredProperty
     * @return array
     */
    public function safes()
    {
        return [
        ];
    }

    public function user($class = User::class)
    {
        return $this->hasOne($class, 'id', 'user_id');
    }

    public function repaymentPlans($class = RepaymentPlan::class)
    {
        return $this->hasMany($class, 'order_id', 'id');
    }

    /**
     * 最新还款计划
     * 注意！：对应第一笔还款计划
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastRepaymentPlan($class = RepaymentPlan::class)
    {
        return $this->hasOne($class, 'order_id', 'id')->orderBy('id')->withDefault();
    }

    /**
     * 订单详情
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderDetails($class = OrderDetail::class)
    {
        return $this->hasMany($class, 'order_id', 'id');
    }

    /**
     * 根据id获取
     * @param $id
     * @return $this|Model|object|null
     */
    public function getOne($id)
    {
        return self::whereId($id)->first();
    }

    public function userInfo($class = UserInfo::class)
    {
        return $this->hasOne($class, 'user_id', 'user_id');
    }

    public function collectionRecords($class = CollectionRecord::class)
    {
        return $this->hasMany($class, 'order_id', 'id');
    }
}
