<?php

namespace Api\Models\Order;

use Admin\Models\Order\RepaymentPlan;
use Api\Models\User\User;
use Api\Models\User\UserAuth;
use Api\Services\Config\ConfigServer;
use Api\Services\Order\OrderCheckServer;
use Api\Services\Order\OrderServer;
use Api\Services\User\UserAuthServer;
use Common\Models\Config\Config;
use Common\Models\Trade\TradeLog;
use Common\Services\Config\LoanMultipleConfigServer;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;
use Common\Utils\Data\DateHelper;
use Common\Utils\Data\MoneyHelper;
use Common\Utils\MerchantHelper;

/**
 * Api\Models\Order\Order
 *
 * @property int $id 订单id
 * @property string|null $order_no 订单编号
 * @property int|null $user_id 用户id
 * @property float|null $principal 贷款金额
 * @property int|null|string $loan_days 贷款天数
 * @property string|null $status 订单状态
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property string|null $app_client APP终端
 * @property string|null $app_version APP版本号
 * @property int|null $quality 新老用户 0新用户 1老用户
 * @property-read RepaymentPlan $lastRepaymentPlan
 * @property-read RepaymentPlan $firstProgressingRepaymentPlan
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Models\Order\OrderDetail[] $orderDetails
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Models\Order\RepaymentPlan[] $repaymentPlans
 * @property-read \Common\Models\User\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereAppClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereAppVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereLoanDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order wherePrincipal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereQuality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereUserId($value)
 * @mixin \Eloquent
 * @property float $daily_rate 日利率
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereDailyRate($value)
 * @property string|null $system_time 机审时间
 * @property string|null $manual_time 人审时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereManualTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereSystemTime($value)
 * @property string|null $paid_time 放款时间
 * @property float|null $paid_amount 实际放款金额
 * @property string|null $pay_channel 放款渠道
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order wherePaidTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order wherePayChannel($value)
 * @property float $overdue_rate 逾期费率
 * @property string|null $signed_time 签约时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereOverdueRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereSignedTime($value)
 * @property string|null $cancel_time 取消时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereCancelTime($value)
 * @property string|null $pass_time 审批通过时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\Order orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order wherePassTime($value)
 * @property string|null $overdue_time 转逾期时间
 * @property string|null $bad_time 转坏账时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereBadTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereOverdueTime($value)
 * @property int|null $merchant_id merchant_id
 * @property int $app_id app_id
 * @property int $approve_push_status 审批推送状态 0 不需要推送 1 未推送 2 已推送
 * @property int $manual_check 是否需要初审, 1需要, 2不需要
 * @property int $call_check 是否需要电审,1需要，2不需要
 * @property string|null $reject_time 被拒时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereApprovePushStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereCallCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereManualCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\Order whereRejectTime($value)
 * @property string|null $apply_principal 申请贷款金额
 * @property int|null $manual_result 人审通过结果
 * @property int|null $call_result 电审通过结果
 * @property string|null $auth_process 认证过程，所属分流
 * @property int|null $nbfc_report_status NBFC上报状态 0:无需上报 1:未上报 2:已上报
 * @property string|null $flag 特殊订单标记
 * @property string|null $reference_no 还款码
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereApplyPrincipal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereAuthProcess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCallResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereManualResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereNbfcReportStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereReferenceNo($value)
 * @property-read \Common\Models\Merchant\Merchant|null $merchant
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
class Order extends \Common\Models\Order\Order
{

    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_INDEX = 'index';
    const SCENARIO_DETAIL = 'detail';
    const SCENARIO_HOME = 'home';
    const SCENARIO_REPAYMENT_PLAN = 'repayment_plan';

    //api对应状态返回
    const STATUS_API_CREATE = 'CREATED'; //订单预创建
    const STATUS_API_PENDING = 'PENDING'; //提交资料审批中  **PESO**
    const STATUS_API_PAYING = 'PAYING'; //放款中&放款审批中 **PESO**
    const STATUS_API_REJECTED = 'REJECTED'; //审批未通过 **PESO**
    const STATUS_API_REPLENISH_ALL = 'NEED_APTITUDE_ALL'; //重新提交资料 ID与face
    const STATUS_API_REPLENISH_ID = 'NEED_APTITUDE_ID'; //重新提交资料 ID
    const STATUS_API_REPLENISH_FACE = 'NEED_APTITUDE_FACE'; //重新提交资料 face
    const STATUS_API_PAY_FAIL = 'PAY_FAILED'; //放款失败
    const STATUS_API_PAID = 'PAID'; //待还款 **PESO**
    const STATUS_API_OVERDUE = 'OVERDUE'; //已逾期 **PESO**
    const STATUS_API_FINISHED = 'FINISHED'; //还款成功
    const STATUS_API_CANCEL = "CANCEL";

    const STATUS_API = [
        self::STATUS_CREATE => self::STATUS_API_CREATE,
        self::STATUS_WAIT_SYSTEM_APPROVE => self::STATUS_API_PENDING,
        self::STATUS_SYSTEM_APPROVING => self::STATUS_API_PENDING,
        self::STATUS_WAIT_MANUAL_APPROVE => self::STATUS_API_PENDING,
        self::STATUS_WAIT_CALL_APPROVE => self::STATUS_API_PENDING,
        self::STATUS_WAIT_TWICE_CALL_APPROVE => self::STATUS_API_PENDING,
        self::STATUS_MANUAL_PASS => self::STATUS_API_PAYING,
        self::STATUS_SYSTEM_PASS => self::STATUS_API_PAYING,
        self::STATUS_SYSTEM_REJECT => self::STATUS_API_REJECTED,
        self::STATUS_MANUAL_REJECT => self::STATUS_API_REJECTED,
        self::STATUS_PAYING => self::STATUS_API_PAYING,
        self::STATUS_SIGN => self::STATUS_API_PAYING,
        self::STATUS_SYSTEM_PAY_FAIL => self::STATUS_API_PAYING,
        self::STATUS_MANUAL_PAY_FAIL => self::STATUS_API_PAYING,
        self::STATUS_MANUAL_CANCEL => self::STATUS_API_CANCEL,
        self::STATUS_USER_CANCEL => self::STATUS_API_CANCEL,
        self::STATUS_SYSTEM_CANCEL => self::STATUS_API_CANCEL,
        self::STATUS_OVERDUE => self::STATUS_API_OVERDUE,
        self::STATUS_SYSTEM_PAID => self::STATUS_API_PAID,
        self::STATUS_MANUAL_PAID => self::STATUS_API_PAID,
        self::STATUS_FINISH => self::STATUS_API_FINISHED,
        self::STATUS_OVERDUE_FINISH => self::STATUS_API_FINISHED,
        self::STATUS_COLLECTION_BAD => self::STATUS_API_PAID,
        self::STATUS_REPAYING => self::STATUS_API_PAID,
    ];
    const STATUS_API_TEXT = [
        self::STATUS_API_CREATE => '预创建',
        self::STATUS_API_PENDING => '审批中',
        self::STATUS_API_PAYING => '放款审核中',
        self::STATUS_API_REJECTED => '审批未通过',
        self::STATUS_API_REPLENISH_ALL => '重新提交资料',
        self::STATUS_API_REPLENISH_ID => '重新提交资料',
        self::STATUS_API_REPLENISH_FACE => '重新提交资料',
        self::STATUS_API_PAID => '待还款',
        self::STATUS_API_FINISHED => '已结清',
        self::STATUS_API_PAY_FAIL => '放款失败',
        self::STATUS_API_CANCEL => '取消申请',
        self::STATUS_API_OVERDUE => '借款已逾期',
    ];

    /** 首页订单不显示状态 */
    const HIDE_IN_USER_HOME = [
        self::STATUS_MANUAL_CANCEL,//人工取消借款
        self::STATUS_USER_CANCEL,//用户取消借款
        self::STATUS_SYSTEM_CANCEL,//系统取消借款
//        self::STATUS_FINISH,//正常结清
//        self::STATUS_OVERDUE_FINISH,//逾期结清
    ];

    public function texts()
    {
        return [
            self::SCENARIO_INDEX => [
                'id',
                'order_no',
                'user_id',
                'principal',
                'loan_days',
                'status',
                'paid_time',
                'paid_amount',
                'apply_principal',
                'created_at',
                'signed_time',
            ],
            self::SCENARIO_DETAIL => [
                'id',
                'order_no',
                'principal',
                'loan_days',
                'created_at',
                'apply_principal',
                'status',
                'daily_rate',
                'signed_time',
                'paid_amount',
                'paid_time',
                'pay_channel',
                'reference_no',
                'withdraw_no',
            ],
            self::SCENARIO_HOME => [
                'id',
                'order_no',
                'principal',
                'loan_days',
                'created_at',
                'status',
                'daily_rate',
                'signed_time',
                'paid_amount',
                'paid_time',
                'pay_channel',
                'created_time',
                'ordered_time',
                # 附加过滤属性
                'processing_fee',
                'apply_principal',
                'penalty_fee',
                'gst_processing_rate',
                'gst_penalty_rate',
                'gst_processing_fee',
                'gst_penalty_fee',
                'processing_fee_incl_gst',
                'overdue_fee_incl_gst',
                'management_fee',
                'app_client',
                'withdraw_no'
            ]
        ];
    }

    public function textRules()
    {
        return [
            'array' => [
                'status' => ts(self::STATUS_ALIAS, 'order')
            ],
            'function' => [
                /** 订单科目 */
                'order_no' => function (\Api\Models\Order\Order $order) {
                    /** @var $order Order */
                    $lastRepaymentPlan = $order->lastRepaymentPlan;
                    $this->subjectCalc = CalcRepaymentSubjectServer::server($lastRepaymentPlan)->getSubject();
                    $this->user = $order->user;
                    $this->processing_fee = MoneyHelper::round2point($order->getProcessingFee());//服务砍头费
                    $this->interest_fee = MoneyHelper::round2point($order->interestFee());//综合费用
                    $this->overdue_fee = MoneyHelper::round2point(empty($lastRepaymentPlan->status) ? 0 : $this->subjectCalc->overdueFee);//逾期息费
                    $this->receivable_amount = MoneyHelper::round2point(empty($lastRepaymentPlan->status) ? $order->principal + $order->interestFee() : $order->repayAmount());//应还金额
                    $this->amount_due = MoneyHelper::round2point($order->amountDue());//本应还金额
                    $this->part_repay_amount = MoneyHelper::round2point($order->getPartRepayAmount());//已部分还款
                    $this->paid_amount = MoneyHelper::round2point($order->paid_amount ?? $order->getPaidAmount());//实际到账金额
                    $this->overdue_days = strval($order->getOverdueDays());//逾期天数
                    $this->appointment_paid_time = DateHelper::format($order->getAppointmentPaidTime(true), 'd-m-Y');//应还时间
                    $this->repay_time = DateHelper::formatToDate($order->lastRepaymentPlan->repay_time, 'd-m-Y') ?? '---';//实际还款时间
                    $this->pay_channel = TradeLog::TRADE_PLATFORM[$order->pay_channel] ?? '---';//放款渠道
                    $this->repay_amount = MoneyHelper::round2point($order->lastRepaymentPlan->repay_amount) ?? '---';//实际还款金额
                    $this->reduction_fee = MoneyHelper::round2point($order->getReductionFee());//减免金额
                    $this->can_cancel = OrderCheckServer::server()->canCancelOrder($order);//能否取消订单
                    $this->can_renewal = OrderCheckServer::server()->canRenewalOrder($order);//能否续期订单
                    $renewal_created_at = (string)optional(optional($order->lastRepaymentPlan)->lastRepaymentPlanRenewal)->created_at;
                    $this->renewal_created_at = DateHelper::formatToDate($renewal_created_at) ?? '---';//续期日期
                    $this->has_renewal = $order->repaymentPlanRenewal->isEmpty() ? false : true;//是否有续期
                    $this->renewal_days = $order->getRenewalDays();//续期天数统计
                    $this->renewal_fee = optional($order->repaymentPlanRenewal)->sum('renewal_fee') ?? '---';//续期费用统计
                    $orderDetail = $order->getOrderDetails();
                    $this->order_detail = $orderDetail ? : null;
                    $this->reference_no = $order->reference_no ?? '---';
                    $this->dg_pay_lifetime_id = $this->userInfo->dg_pay_lifetime_id ?? "---";
                    $order->lastTradeLog && $order->lastTradeLog->getText([
                        'bank_name',
                        'trade_account_no',
                        'trade_desc',
                        'trade_request_time',
                        'business_amount',
                    ]);

                    //拒绝天数
                    if (in_array($this->status, [Order::STATUS_MANUAL_REJECT, Order::STATUS_SYSTEM_REJECT])) {
                        $this->rejected_days_left = OrderServer::server()->getRejectLastDays($order);
                    }

                    if (in_array($this->scenario, [self::SCENARIO_HOME, self::SCENARIO_REPAYMENT_PLAN])) {
                        $this->repaymentPlan($order);
                    }

                    # 部分还款 第一期
                    $this->show_part_repay = false;
                    $this->min_part_repay = $this->receivable_amount;
                    $this->can_part_repay = false;
                    # 已经部分还款了，展示部分还款字段
                    if ($this->part_repay_amount > 0) {
                        $this->show_part_repay = true;
                    }
                    # 订单第一期，并且可部分还款
                    if (optional($order->firstProgressingRepaymentPlan)->installment_num == 1 && ConfigServer::server()->getPartRepayOn($order)) {
                        $this->show_part_repay = true;
                        $this->min_part_repay = ConfigServer::server()->getMinPartRepay($order);
                        # 还款页是否可操作部分还款
                        if ($this->min_part_repay < $this->receivable_amount) {
                            $this->can_part_repay = true;
                        }
                    }

                    # 最后换算
                    $this->paid_time = DateHelper::formatToDate($order->paid_time, 'd-m-Y') ?? '---';//实际到账时间|放款时间
                    $this->created_at = DateHelper::formatToDate($order->created_at, 'd-m-Y') ?? '---';//申请时间
                    $this->signed_time = DateHelper::formatToDate($order->signed_time, 'd-m-Y') ?? '---';//签约时间
//                    $this->principal = strval($order->getPaidPrincipal());//当前应还本金

                    unset($order->orderDetails, $order->lastRepaymentPlan);
                },
                'id' => function () {
                    $this->api_status = $this->getApiStatus();
                    $this->api_status_text = array_get(self::STATUS_API_TEXT, $this->api_status);
                },
                'daily_rate' => function () {
                    return $this->daily_rate * 100 . '%';
                },
            ]
        ];
    }

    /**
     * 还款计划
     *
     * @param $order
     */
    public function repaymentPlan(&$order)
    {
        foreach ($order->repaymentPlans as &$repaymentPlan) {
            $repaymentPlan->setScenario(RepaymentPlan::SCENARIO_LIST)->getText();
        }
        # 分期
        $orderInstallment = OrderDetail::model()->getInstallment($order);
        if ($orderInstallment) {
            $orderInstallment = json_decode($orderInstallment, true);
            # 是否分期
            $order->is_installment = true;
            # 期数
            $order->installment_count = count($orderInstallment);
            # 首页可减免按钮展示
            $order->can_deduction = false;
            # 当前期数
            if ($firstProgressingRepaymentPlan = $order->firstProgressingRepaymentPlan) {
                $order->current_installment_num = $firstProgressingRepaymentPlan->installment_num;
                $lastInstallmentNun = 2;
                # 仅剩第2期，且第二期借款天数为60天
                if ($firstProgressingRepaymentPlan->installment_num == $lastInstallmentNun
                    && $firstProgressingRepaymentPlan->loan_days == Config::VALUE_MAX_LOAN_DAY) {
                    $order->can_deduction = true;
                }
            } else {
                $order->current_installment_num = 0;
            }
            # 终端跳转可减免页判断参数 is_can_reduction_installment
            /*$order->is_can_reduction_installment = false;
            if ($order->installment_count == $order->current_installment_num) {
                $order->is_can_reduction_installment = true;
            }*/
        } else {
            $order->is_installment = false;
        }
    }

    /**
     *
     * @suppress PhanUndeclaredProperty
     * @return array
     */
    public function safes()
    {
        /** @var $user User */
        $user = \Auth::user();
        return [
            self::SCENARIO_CREATE => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'app_id' => MerchantHelper::getAppId(),
                'order_no' => Order::generateOrderNo(),
                'user_id' => $user->id,
                'principal' => LoanMultipleConfigServer::server()->getLoanAmountMax($user),
                'loan_days' => LoanMultipleConfigServer::server()->getLoanDaysMax($user),
                'app_client',
                'app_version',
                'management_fee',
                'quality',
                //'status' => self::STATUS_WAIT_SYSTEM_APPROVE,
                //'status' => \Common\Services\Order\OrderServer::server()->getOrderCreateStatus($user),
                'apply_principal',
                'status',
                'approve_push_status',
                'manual_check',
                'call_check',
                'daily_rate' => LoanMultipleConfigServer::server()->getDailyRate($user),
                'created_at' => $this->getDate(),
                'updated_at' => $this->getDate(),
                'overdue_rate' => LoanMultipleConfigServer::server()->getPenaltyRate($user),
//                'auth_process' => OrderServer::server()->getAuthProcess($this, $user),
                //'nbfc_report_status' => NbfcReportServer::server()->needReportNbfc($user->merchant_id) ? self::NBFC_REPORT_STATUS_NO : self::NBFC_REPORT_STATUS_NO_NEED,
            ],
            self::SCENARIO_UPDATE => [
                'principal',
                'loan_days',
                'updated_at' => $this->getDate(),
            ],
        ];
    }

    public function getList($param)
    {
        $where = [
            'user_id' => $param['user_id'],
        ];
        $query = $query = $this->newQuery();
        $query->where($where)->whereNotIn("status", [Order::STATUS_CREATE]);
//        if (!array_get($param, 'status')) {
//            $query->whereIn('status', [Order::STATUS_FINISH, Order::STATUS_OVERDUE_FINISH]);
//        }
        $size = array_get($param, 'size');
        return $query->orderBy('id', 'desc')->paginate($size);
    }

    public function getOneByUser($orderId, $userId)
    {
        return $this->whereId($orderId)->whereUserId($userId)->first();
    }

    public function create($data)
    {
        $data['app_client'] = $data['app_client'] ?? array_get($data, 'client_id');
        return Order::model()->setScenario(self::SCENARIO_CREATE)->saveModel($data);
    }

    public function getApiStatus()
    {
        if ($this->isRejected()) {
            return self::STATUS_API_REJECTED;
        }
        /** 处理补件逻辑 */
        $faceStatus = UserAuthServer::server()->getAuthStatus($this->user_id, UserAuth::TYPE_FACES);
        $idCardStatus = UserAuthServer::server()->getAuthStatus($this->user_id, UserAuth::TYPE_ID_FRONT);
        if ($this->status == self::STATUS_REPLENISH) {
            /** 身份证&自拍都需要补件 */
            if (!$faceStatus && !$idCardStatus) {
                return self::STATUS_API_REPLENISH_ALL;
            } elseif (!$faceStatus) { //仅自拍
                return self::STATUS_API_REPLENISH_FACE;
            } elseif (!$idCardStatus) { //仅身份证
                return self::STATUS_API_REPLENISH_ID;
            }
        }
        return array_get(self::STATUS_API, $this->status);
    }

    /**
     * 隐藏订单判断
     *
     * @return bool
     */
    public function checkHideOrder()
    {
        # 首页订单不显示状态
        if (in_array($this->status, Order::HIDE_IN_USER_HOME)) {
            return true;
        }
        # 超过被拒天数
//        if (in_array($this->status, [
//                Order::STATUS_SYSTEM_REJECT,
//                Order::STATUS_MANUAL_REJECT
//            ]) && !$this->isRejected()) {
//            return true;
//        }
        return false;
    }

    /**
     * 关联续期表
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function repaymentPlanRenewal($class = RepaymentPlanRenewal::class)
    {
        return parent::repaymentPlanRenewal($class);
    }

    public function repaymentPlans($class = RepaymentPlan::class)
    {
        return parent::repaymentPlans($class);
    }

    public function getLastOrderByUid($userId)
    {
        return self::where([
            'user_id' => $userId,
        ])->orderBy('created_at', 'desc')->first();
    }

}
