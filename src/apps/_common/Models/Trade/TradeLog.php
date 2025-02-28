<?php

namespace Common\Models\Trade;

use Carbon\Carbon;
use Common\Models\BankCard\BankCardPeso;
use Common\Models\Order\Order;
use Common\Models\User\User;
use Common\Traits\Model\StaticModel;
use Common\Utils\Data\DateHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Order\TradeLog
 *
 * @property int $id 交易记录自增长id
 * @property string $trade_platform 交易平台
 * @property int $user_id 用户id
 * @property int $master_related_id 业务主关联id (订单id)
 * @property string $master_business_no 主业务编号(订单编号)
 * @property string $batch_no 交易批次号
 * @property string $transaction_no 交易编号(生成唯一编号)
 * @property string $request_no 支付系统定义支付请求编号
 * @property string $trade_platform_no 第三方交易平台交易编号
 * @property string $business_type 业务类型 manual_remit:人工出款  repay:还款
 * @property int $trade_type 交易类型( 1:出款, 2:回款, 3:退款)
 * @property int $request_type 发起类型 1:系统  2:管理后台 3:用户
 * @property float $business_amount 业务金额
 * @property float $trade_amount 交易金额
 * @property string $bank_name 银行名称
 * @property string $trade_account_telephone 交易账户手机号(银行卡手机号、支付宝手机号)
 * @property string $trade_account_name 交易账户名(银行卡账户、支付宝姓名)
 * @property string $trade_account_no 交易账户号(用户银行卡、支付宝账户)
 * @property string $trade_desc 交易描述
 * @property string $trade_result_time 交易时间
 * @property string $trade_notice_time 交易通知时间
 * @property string $trade_settlement_time 清算时间
 * @property string $trade_request_time 请求时间
 * @property int $p_id 父id,退款用
 * @property int $admin_id 后台管理员id
 * @property string $handle_name 操作者名称
 * @property int $trade_evolve_status 交易进展状态
 * @property int $trade_result 交易结果(1成功,2失败)
 * @property \Illuminate\Support\Carbon $updated_at 更新时间
 * @property \Illuminate\Support\Carbon $created_at 添加时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereBatchNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereBusinessAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereBusinessType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereHandleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereMasterBusinessNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereMasterRelatedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog wherePId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereRequestNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereRequestType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereTradeAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereTradeAccountNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereTradeAccountTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereTradeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereTradeDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereTradeEvolveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereTradeNoticeTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereTradePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereTradePlatformNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereTradeRequestTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereTradeResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereTradeResultTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereTradeSettlementTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereTradeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereTransactionNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereUserId($value)
 * @mixin \Eloquent
 * @property-read \Common\Models\Order\Order $order
 * @property-read \Common\Models\BankCard\BankCardPeso $bankCard
 * @property-read \Common\Models\User\User $user
 * @property-read \Common\Models\Trade\AdminTradeAccount $adminTradeAccount
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereAdminTradeAccountId($value)
 * @property int $merchant_id merchant_id
 * @property int $bank_card_id 银行卡id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereBankCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLog whereMerchantId($value)
 * @property string|null $trade_result_code 交易结果CODE
 * @method static Builder|TradeLog orderByCustom($defaultSort = null)
 * @method static Builder|TradeLog whereTradeResultCode($value)
 * @property string|null $withdraw_no 取款码
 * @property string|null $reference_no 还款码
 * @method static Builder|TradeLog whereReferenceNo($value)
 * @method static Builder|TradeLog whereWithdrawNo($value)
 * @property int $admin_trade_account_id 关联 admin_pay_account
 */
class TradeLog extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'trade_log';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];

    /** 第三方平台：手动支付|线下支付 */
    const TRADE_PLATFORM_MANUAL = 'manual';
    /** SAAS平台：自动减免 */
    const TRADE_PLATFORM_DEDUCTION = 'deduction';
    /** SAAS平台：手动减免 */
    const TRADE_PLATFORM_MANUAL_DEDUCTION = 'manual_deduction';
    /** 第三方平台 */
    const TRADE_PLATFORM_FAWRY = "fawry";
    const TRADE_PLATFORM_PAYMOB = 'Paymob';
    const TRADE_PLATFORM = [
        self::TRADE_PLATFORM_MANUAL => 'manual',
        self::TRADE_PLATFORM_FAWRY => "Fawry",
    ];

    /** 支持代付功能的平台 */
    const TRADE_PLATFORM_HAS_PAYOUT = [
        self::TRADE_PLATFORM_PAYMOB,
    ];

    /** 支持还款功能的平台 */
    const TRADE_PLATFORM_HAS_REPAY = [
        self::TRADE_PLATFORM_MANUAL,
        self::TRADE_PLATFORM_DEDUCTION,
        self::TRADE_PLATFORM_MANUAL_DEDUCTION,
        self::TRADE_PLATFORM_FAWRY
    ];

    /** 业务类型：出款 */
    const BUSINESS_TYPE_MANUAL_REMIT = 'remit';
    /** 业务类型：还款 */
    const BUSINESS_TYPE_REPAY = 'repay';
    /** 业务类型：续期 */
    const BUSINESS_TYPE_RENEWAL = 'renewal';
    /** 业务类型 */
    const BUSINESS_TYPE = [
        self::BUSINESS_TYPE_MANUAL_REMIT => '放款',
        self::BUSINESS_TYPE_REPAY        => '还款',
        //self::BUSINESS_TYPE_RENEWAL => '续期',
    ];

    /** 交易类型：出款 */
    const TRADE_TYPE_REMIT = 1;
    /** 交易类型：入款 */
    const TRADE_TYPE_RECEIPTS = 2;
    /** 交易类型：退款 */
    const TRADE_TYPE_REFUND = 3;
    /** 交易类型 */
    const TRADE_TYPE = [
        self::TRADE_TYPE_REMIT    => '出款',
        self::TRADE_TYPE_RECEIPTS => '入款',
        self::TRADE_TYPE_REFUND   => '退款',
    ];

    /** 请求来源：系统 */
    const REQUEST_TYPE_SYSTEM = 1;
    /** 请求来源：管理后台 */
    const REQUEST_TYPE_ADMIN = 2;
    /** 请求来源：用户 */
    const REQUEST_TYPE_USER = 3;
    /** 请求来源 */
    const REQUEST_TYPE = [
        self::REQUEST_TYPE_SYSTEM => '系统',
        self::REQUEST_TYPE_ADMIN  => '管理后台',
        self::REQUEST_TYPE_USER   => '用户',
    ];

    /** 交易进展状态 - 交易创建 */
    const TRADE_EVOLVE_STATUS_CREATED = 0;
    /** 交易进展状态 - 已发送短验(待交易) */
    const TRADE_EVOLVE_STATUS_SEND_CODE = 1;
    /** 交易进展状态 - 交易中 */
    const TRADE_EVOLVE_STATUS_TRADING = 2;
    /** 交易进展状态 - 交易结束 */
    const TRADE_EVOLVE_STATUS_OVER = 3;
    /** 交易进展状态 - 短验发送失败 */
    const TRADE_EVOLVE_STATUS_CODE_FAILED = 4;
    /** 交易进展状态 - 短验验证失败 */
    const TRADE_EVOLVE_STATUS_CODE_VERIFY_FAILED = 5;
    /** 交易进展状态 - 交易已退款 */
    const TRADE_EVOLVE_STATUS_IS_REFUND = 6;
    /** 交易进展状态 - 交易闲置 */
    const TRADE_EVOLVE_STATUS_UNUSED = 7;
    /** 交易进展状态 */
    const TRADE_EVOLVE_STATUS = [
        self::TRADE_EVOLVE_STATUS_CREATED            => '交易创建',
        self::TRADE_EVOLVE_STATUS_SEND_CODE          => '已发短验',
        self::TRADE_EVOLVE_STATUS_TRADING            => '交易中',
        self::TRADE_EVOLVE_STATUS_OVER               => '交易结束',
        self::TRADE_EVOLVE_STATUS_CODE_FAILED        => '短验发送失败',
        self::TRADE_EVOLVE_STATUS_CODE_VERIFY_FAILED => '短验验证失败',
        self::TRADE_EVOLVE_STATUS_IS_REFUND          => '交易已退款',
        self::TRADE_EVOLVE_STATUS_UNUSED             => '交易闲置',
    ];

    const TRADE_RESULT_NULL = 0;
    /** 交易结果 - 成功 */
    const TRADE_RESULT_SUCCESS = 1;
    /** 交易结果 - 失败 */
    const TRADE_RESULT_FAILED = 2;
    /** 交易结果 */
    const TRADE_RESULT = [
        self::TRADE_RESULT_SUCCESS => '支付成功',
        self::TRADE_RESULT_FAILED  => '支付失败',
        self::TRADE_RESULT_NULL    => '处理中',
    ];

    /** CODE分类：0XXX：成功 1XXX：第三方错误  2XXX：支付系统错误  3XXX：用户信息错误 */
    /** 交易结果CODE：成功 */
    const RESULT_CODE_SUCCESS = '0001';

    /** 交易结果CODE：第三方错误 */
    const RESULT_CODE_ER_1001 = '1001';
    /** 交易结果CODE：余额不足 */
    const RESULT_CODE_ER_1002 = '1002';

    /** 交易结果CODE：系统错误 */
    const RESULT_CODE_ER_2001 = '2001';

    /** 交易结果CODE：用户信息错误 */
    const RESULT_CODE_ER_3001 = '3001';
    /** 交易结果CODE：放款拦截 */
    const RESULT_CODE_ER_3002 = '3002';
    
    const WITHDRAWAL_INSTITUTION_01 = 'MLH';
    const WITHDRAWAL_INSTITUTION_02 = 'RDP';
    const WITHDRAWAL_INSTITUTION_03 = 'CEBU';
    const WITHDRAWAL_INSTITUTION_04 = 'PeraHub';
    const WITHDRAWAL_INSTITUTION_05 = 'LBC';
    const WITHDRAWAL_INSTITUTION_06 = 'VLRC';
    
    const WITHDRAWAL_INSTITUTION_SKYPAY = [
        self::WITHDRAWAL_INSTITUTION_01,
        self::WITHDRAWAL_INSTITUTION_02,
    ];
    
    const WITHDRAWAL_INSTITUTION_DRAGONPAY = [
        self::WITHDRAWAL_INSTITUTION_01,
        self::WITHDRAWAL_INSTITUTION_02,
        self::WITHDRAWAL_INSTITUTION_03,
        self::WITHDRAWAL_INSTITUTION_04,
//        self::WITHDRAWAL_INSTITUTION_05,
        self::WITHDRAWAL_INSTITUTION_06,
    ];

    /**
     * 放款失败后需要重新放款的code
     */
    const NEED_AFRESH_TRADE_RESULT_CODE = [
        self::RESULT_CODE_ER_1002,
    ];

    /** 交易处理者:系统 */
    const HANDLER_SYSTEM = -1;

    const SCENARIO_CREATE = 'create';

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id',
                'batch_no',
                'transaction_no',
                'trade_evolve_status' => self::TRADE_EVOLVE_STATUS_CREATED,
                'trade_result'        => self::TRADE_RESULT_NULL,

                'trade_platform',
                'user_id',
                'admin_trade_account_id',
                'bank_card_id',
                'master_related_id',
                'master_business_no',
                //'request_no' => '',
                //'trade_platform_no' => '',
                'business_type',
                'trade_type',
                'request_type',
                'business_amount',
                'trade_amount',
                'bank_name',
                'trade_account_telephone',
                'trade_account_name',
                'trade_account_no',
                'trade_desc',
                //'trade_result_time' => $params['trade_result_time'],
                //'trade_notice_time' => $params['trade_notice_time'],
                //'trade_settlement_time' => $params['trade_settlement_time'],
                //'trade_request_time' => $params['trade_request_time'],
                'admin_id',
                'handle_name',
            ],
        ];
    }

    public function textRules()
    {
        return [
            'array'    => [
                'trade_platform'      => self::TRADE_PLATFORM,
                'business_type'       => ts(self::BUSINESS_TYPE, 'pay'),
                'trade_type'          => self::TRADE_TYPE,
                'request_type'        => self::REQUEST_TYPE,
                'trade_evolve_status' => self::TRADE_EVOLVE_STATUS,
            ],
            'function' => [
                'trade_result' => function ($model) {
                    if ($model->trade_evolve_status == self::TRADE_EVOLVE_STATUS_UNUSED && $model->trade_result == self::TRADE_RESULT_NULL) {
                        return t(array_get(self::TRADE_EVOLVE_STATUS, $model->trade_evolve_status), 'pay');
                    }
                    return t(array_get(self::TRADE_RESULT, $model->trade_result), 'pay');
                }
            ],
        ];
    }

    public function sortCustom()
    {
        return [
            'trade_result_time' => [
                'field' => 'trade_result_time',
            ],
        ];
    }

    /**
     * @param $id
     * @return $this|Model|object|null
     */
    public function getOne($id)
    {
        return self::whereId($id)->first();
    }

    /**
     *
     * @param $params
     * @param array $detailParams
     * @return bool|TradeLog
     */
    public function add($params, array $detailParams = [])
    {
        $data     = [
            'merchant_id'             => $params['merchant_id'],
            'batch_no'                => self::generateBatchNo(),
            'transaction_no'          => self::generateTransactionNo(),
            'trade_platform'          => $params['trade_platform'],
            'admin_trade_account_id'  => $params['admin_trade_account_id'],
            'user_id'                 => $params['user_id'],
            'bank_card_id'            => $params['bank_card_id'] ?? 0,
            'master_related_id'       => $params['master_related_id'],
            'master_business_no'      => $params['master_business_no'],
            //'request_no' => '',
            //'trade_platform_no' => '',
            'business_type'           => $params['business_type'],
            'trade_type'              => $params['trade_type'],
            'request_type'            => $params['request_type'],
            'business_amount'         => $params['business_amount'],
            'trade_amount'            => $params['trade_amount'] ?? '',
            'bank_name'               => $params['bank_name'] ?? '',
            'trade_account_telephone' => $params['trade_account_telephone'] ?? '',
            'trade_account_name'      => $params['trade_account_name'] ?? '',
            'trade_account_no'        => $params['trade_account_no'] ?? '',
            'trade_desc'              => $params['trade_desc'],
            //'trade_result_time' => $params['trade_result_time'],
            //'trade_notice_time' => $params['trade_notice_time'],
            //'trade_settlement_time' => $params['trade_settlement_time'],
            //'trade_request_time' => $params['trade_request_time'],
            'admin_id'                => $params['admin_id'] ?? 0,
            'handle_name'             => $params['handle_name'] ?? '',
        ];
        $tradeLog = self::model(self::SCENARIO_CREATE)->saveModel($data);

        array_walk($detailParams, function (&$item) use ($tradeLog) {
            $item['trade_log_id'] = $tradeLog->id;
        });
        TradeLogDetail::model(TradeLogDetail::SCENARIO_CREATE)->saveModels($detailParams, true, true);

        return $tradeLog;
    }

    /**
     * 支付中
     * @param string $requestNo
     * @param string $tradePlatformNo
     * @return $this
     */
    public function evolveStatusTrading($requestNo = '', $tradePlatformNo = '')
    {
        $this->trade_evolve_status = self::TRADE_EVOLVE_STATUS_TRADING;
        $this->trade_request_time  = DateHelper::dateTime();

        if ($requestNo) {
            $this->request_no = $requestNo;
        }

        if ($tradePlatformNo) {
            $this->trade_platform_no = $tradePlatformNo;
        }

        $this->save();

        return $this;
    }

    /**
     * 交易结束 - 成功
     * @param $tradeNo
     * @param string $tradeResultTime 交易时间
     * @param string $tradeSettlementTime 清算时间
     * @param $tradeAmount
     * @return $this
     */
    public function evolveStatusOverResultSuccess($tradeNo, $tradeResultTime, $tradeSettlementTime, $tradeAmount = null)
    {
        $this->trade_evolve_status = self::TRADE_EVOLVE_STATUS_OVER;
        $this->trade_result        = self::TRADE_RESULT_SUCCESS;
        $this->trade_notice_time   = DateHelper::dateTime();

        if ($tradeNo) {
            $this->trade_platform_no = $tradeNo;
        }

        if ($tradeResultTime) {
            $this->trade_result_time = $tradeResultTime;
        }

        if ($tradeSettlementTime) {
            $this->trade_settlement_time = $tradeSettlementTime;
        }
        if ($tradeAmount) {
            $this->trade_amount = $tradeAmount;
        }

        $this->save();

        return $this;
    }

    /**
     * 交易结束 - 失败
     * @param $requestNo
     * @param $tradeNo
     * @param $tradeResultTime
     * @param $desc
     * @param $resultCode
     * @return $this
     */
    public function evolveStatusOverResultFailed($requestNo, $tradeNo, $tradeResultTime, $desc = null, $resultCode = null)
    {
        $this->trade_evolve_status = self::TRADE_EVOLVE_STATUS_OVER;
        $this->trade_result        = self::TRADE_RESULT_FAILED;

        if ($requestNo) {
            $this->request_no = $requestNo;
        }

        if ($tradeResultTime) {
            $this->trade_result_time = $tradeResultTime;
        }

        if ($tradeNo) {
            $this->trade_platform_no = $tradeNo;
        }

        if ($resultCode) {
            $this->trade_result_code = $resultCode;
        }

        if ($desc) {
            $this->trade_desc = $desc;
        }

        $this->save();

        return $this;
    }

    public function evolveStatusToUnused()
    {
        $this->trade_evolve_status = self::TRADE_EVOLVE_STATUS_UNUSED;

        return $this->save();
    }

    public function isSuccess()
    {
        return $this->isOver() && $this->trade_result == self::TRADE_RESULT_SUCCESS;
    }

    public function isFailed()
    {
        return $this->isOver() && $this->trade_result == self::TRADE_RESULT_FAILED;
    }

    public function isOver()
    {
        return $this->trade_evolve_status == self::TRADE_EVOLVE_STATUS_OVER;
    }

    public function isTrading()
    {
        return $this->trade_evolve_status = self::TRADE_EVOLVE_STATUS_TRADING;
    }

    /**
     * 根据批次号获取记录
     * @param $batchNo
     * @return mixed
     */
    public static function getByBatchNo($batchNo)
    {
        return static::where('batch_no', $batchNo)->first();
    }

    /**
     * 根据交易号获取记录
     * @param $transactionNo
     * @return \Illuminate\Database\Eloquent\Builder|Model|object|null|self
     */
    public static function getByTransactionNo($transactionNo)
    {
        return self::query()->where(['transaction_no' => $transactionNo])->first();
    }

    /**
     * 生成transactionNo
     * @return string
     */
    public static function generateTransactionNo()
    {
        $transactionNo = strtoupper(uniqid() . date('Ymd'));

        if (self::getByTransactionNo($transactionNo)) {
            return self::generateTransactionNo();
        }

        return $transactionNo;
    }

    /**
     * 生成批次编号
     * @return string
     */
    public static function generateBatchNo()
    {
        $batchNo = date('YmdHis') . mt_rand(100, 999);

        if (self::getByBatchNo($batchNo)) {
            return self::generateBatchNo();
        }

        return $batchNo;
    }

    public static function getDailyRemitAmount($date = null)
    {
        $dateCarbon = Carbon::today();
        if (!is_null($date)) {
            $dateCarbon = Carbon::parse($date);
        }

        $tradeLogs = TradeLog::getDailyRemitTrade([$dateCarbon->toDateString(), $dateCarbon->addDay()->toDateString()]);

        $dayPaidAmount = $tradeLogs->sum('business_amount');

        return $dayPaidAmount;
    }

    /**
     * 获取指定时间内 出款成功 or 出款中的交易
     * @param array $createdAtBetween
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getDailyRemitTrade(array $createdAtBetween = [])
    {
        $query = self::getSuccessOrTradingTrade();

        $where = [
            'business_type' => self::BUSINESS_TYPE_MANUAL_REMIT,
        ];

        $query->where($where);

        if ($createdAtBetween) {
            $query->whereBetween('created_at', $createdAtBetween);
        }

        return $query->get();
    }

    /**
     * 获取交易成功or交易中的交易 query
     * @return Builder
     */
    public static function getSuccessOrTradingTrade()
    {
        $query = self::query()
            ->where(function (Builder $query) {
                $query->where('trade_evolve_status', self::TRADE_EVOLVE_STATUS_TRADING)
                    ->orWhere(function (Builder $query) {
                        $query->where('trade_evolve_status', self::TRADE_EVOLVE_STATUS_OVER)
                            ->where('trade_result', self::TRADE_RESULT_SUCCESS);
                    });
            });

        return $query;
    }

    public function isNeedAfreshTrade()
    {
        return $this->trade_type == self::TRADE_TYPE_REMIT && $this->isFailed() &&
            in_array($this->trade_result_code, self::NEED_AFRESH_TRADE_RESULT_CODE);
    }

    public function isInsufficientBalance()
    {
        return $this->trade_result_code == self::RESULT_CODE_ER_1002;
    }

    public function order($class = Order::class)
    {
        return $this->hasOne($class, 'id', 'master_related_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function adminTradeAccount()
    {
        return $this->hasOne(AdminTradeAccount::class, 'id', 'admin_trade_account_id');
    }

    public function tradeLogDetail($class = TradeLogDetail::class)
    {
        return $this->hasMany($class, 'trade_log_id', 'id');
    }

    public function bankCard($class = BankCardPeso::class)
    {
        return $this->hasOne($class, 'id', 'bank_card_id');
    }
}
