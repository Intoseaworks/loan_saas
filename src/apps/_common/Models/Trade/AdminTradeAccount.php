<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/28
 * Time: 10:38
 */

namespace Common\Models\Trade;


use Admin\Rules\TradeManage\AccountRule;
use Common\Traits\Model\StaticModel;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Trade\AdminTradeAccount
 *
 * @property int $id id
 * @property int $type 类型 1:放款  2:还款
 * @property int $payment_method 类型 1:银行卡转账  2:支付宝
 * @property string $account_no 银行卡号
 * @property string $account_name 银行卡户名
 * @property int $is_default 是否默认  1:是  0:否
 * @property int $status 状态  1:正常  -1:已删除  -2:禁用
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\AdminTradeAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\AdminTradeAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\AdminTradeAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\AdminTradeAccount whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\AdminTradeAccount whereAccountNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\AdminTradeAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\AdminTradeAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\AdminTradeAccount whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\AdminTradeAccount wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\AdminTradeAccount whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\AdminTradeAccount whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\AdminTradeAccount whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $bank_name 银行名称
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\AdminTradeAccount whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\AdminTradeAccount orderByCustom($column = null, $direction = 'asc')
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\AdminTradeAccount whereMerchantId($value)
 */
class AdminTradeAccount extends Model
{
    use StaticModel;
    const SCENE_LIST = 'list';
    const SCENE_OPTION = 'option';
    const SCENE_SIMPLE = 'simple';

    const SCENARIO_CREATE = 'create';
    /**
     * @var string
     */
    protected $table = 'admin_trade_account';

    /** 账号类型 */
    const TYPE_ALIAS = [
        self::TYPE_OUT => '放款',
        self::TYPE_IN => '还款',
    ];
    const TYPE_OUT = 1;
    const TYPE_IN = 2;
    /** 支付方式Alias */
    const PLATFORM_ALIAS = [
        /*self::PAYMENT_METHOD_BANK => '银行转账',
        self::PAYMENT_METHOD_ALIPAY => '支付宝转账',
        self::PAYMENT_METHOD_WEIXIN => '微信银行卡转账',
        self::PAYMENT_METHOD_FUIOU_DAIFU => '富友代付',
        self::PAYMENT_METHOD_FUIOU_DAIKOU => '富友代扣',*/
        self::PAYMENT_METHOD_MOBIKWIK => 'Mobikwik',
        self::PAYMENT_METHOD_RAZORPAY => 'Razorpay',
        self::PAYMENT_METHOD_PAYTM => 'PayTm',
        self::PAYMENT_METHOD_MPURSE => 'Mpurse',
        self::PAYMENT_METHOD_DEDUCTION => 'Application of deduction',
    ];
    /** 放款方式list */
    const PAYMENT_PLATFORM = [
        self::PAYMENT_METHOD_BANK,
    ];
    /** 收款方式list */
    const REPAYMENT_PLATFORM = [
//        self::PAYMENT_METHOD_MOBIKWIK,
        self::PAYMENT_METHOD_RAZORPAY,
        self::PAYMENT_METHOD_MPURSE,
    ];
    /** app 还款支持的平台 */
    const REPAYMENT_APP_REPAY_PLATFORM = [
//        self::PAYMENT_METHOD_MOBIKWIK,
        self::PAYMENT_METHOD_MPURSE,
    ];
    /** 代付渠道list */
    const PAYMENT_DAIFU = [
//        self::PAYMENT_METHOD_FUIOU_DAIFU
    ];
    /** 代扣渠道list */
    const PAYMENT_DAIKOU = [
//        self::PAYMENT_METHOD_FUIOU_DAIKOU
    ];

    const PAYMENT_METHOD_BANK = 1;
    const PAYMENT_METHOD_PAYTM = 2;
    const PAYMENT_METHOD_MPURSE = 3;
    const PAYMENT_METHOD_FUIOU_DAIFU = 4;
    const PAYMENT_METHOD_FUIOU_DAIKOU = 5;
    const PAYMENT_METHOD_MOBIKWIK = 6;
    const PAYMENT_METHOD_RAZORPAY = 7;
    // 手动支付
    const PAYMENT_METHOD_MANUAL = 8;
    // 减免
    const PAYMENT_METHOD_DEDUCTION = 9;
    /** 状态 */
    const STATUS_ALIAS = [
        self::STATUS_ACTIVE => '启用',
        self::STATUS_DELETE => '禁用',
    ];
    const STATUS_ACTIVE = 1;
    const STATUS_DELETE = 0;

    /**
     * 支付方式 关联 第三方
     */
    const PAYMENT_METHOD_RELATE_PLATFORM = [
        self::PAYMENT_METHOD_MANUAL => TradeLog::TRADE_PLATFORM_MANUAL,
        self::PAYMENT_METHOD_DEDUCTION => TradeLog::TRADE_PLATFORM_DEDUCTION,
    ];

    const IS_DEFAULT = 1;//默认
    const IS_NOT_DEFAULT = 0;

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function texts()
    {
        return [
            self::SCENE_LIST => [
                'id',
                'type',
                'account_no',
                'account_name',
                'payment_method',
                'status',
                'created_at',
                'is_default',
            ],
            self::SCENE_OPTION => [
                'id',
                'account_no',
                'account_name',
                'payment_method',
                'bank_name',
            ],
            self::SCENE_SIMPLE => [
                'account_no',
            ],
        ];
    }

    public function textRules()
    {
        return [
            'array' => [
                'type' => ts(self::TYPE_ALIAS, 'pay'),
                'payment_method' => self::PLATFORM_ALIAS,
                'status' => ts(self::STATUS_ALIAS),
            ]
        ];
    }

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'type',
                'payment_method',
                'account_no',
                'account_name',
                'status',
                'bank_name',
            ],
            AccountRule::SCENARIO_CHANGE_STATUS => ['status'],
            AccountRule::SCENARIO_CHANGE_DEFAULT => ['is_default']
        ];
    }

    /**
     * 查询构造器
     * @param $params
     * @param array $with
     * @return AdminTradeAccount|\Illuminate\Database\Eloquent\Builder
     */
    public function search($params, $with = [])
    {
        $query = self::query()->with($with);
        if ($type = array_get($params, 'type')) {
            $query->whereType($type);
        }
        if ($paymentMethod = array_get($params, 'payment_method')) {
            $query->whereIn('payment_method', (array)$paymentMethod);
        }
        if ($accountNo = array_get($params, 'account_no')) {
            $query->whereAccountNo($accountNo);
        }
        if ($status = array_get($params, 'status')) {
            $query->whereStatus($status);
        }
        return $query;
    }

    /**
     * @param $id
     * @param $where
     * @return \Illuminate\Database\Eloquent\Builder|$this|object|null
     */
    public function getOne($id, $where = [])
    {
        return self::query()->whereKey($id)
            ->where($where)
            ->first();
    }

    public function enable()
    {
        return $this->setScenario(AccountRule::SCENARIO_CHANGE_STATUS)->saveModel(['status' => self::STATUS_ACTIVE]);
    }

    public function disable()
    {
        return $this->setScenario(AccountRule::SCENARIO_CHANGE_STATUS)->saveModel(['status' => self::STATUS_DELETE]);
    }

    public function enableDefault()
    {
        return $this->setScenario(AccountRule::SCENARIO_CHANGE_DEFAULT)->saveModel(['is_default' => self::IS_DEFAULT]);
    }

    public function disableDefault()
    {
        return $this->setScenario(AccountRule::SCENARIO_CHANGE_DEFAULT)->saveModel(['is_default' => self::IS_NOT_DEFAULT]);
    }

    public function disableDefaultAll()
    {
        return self::where('is_default', self::IS_DEFAULT)->update(['is_default' => self::IS_NOT_DEFAULT]);
    }

    /**
     * 根据卡号动态创建后台手动支付账号
     * @param $adminTradeAccountNo
     * @return AdminTradeAccount
     */
    public static function createManualAccount($adminTradeAccountNo)
    {
        $adminTradeAccountNo = trim($adminTradeAccountNo);
        $model = AdminTradeAccount::firstOrCreateModel(AdminTradeAccount::SCENARIO_CREATE, [
            'account_no' => $adminTradeAccountNo,
            'type' => self::TYPE_OUT,
            'payment_method' => self::PAYMENT_METHOD_MANUAL,
        ], [
            'status' => self::STATUS_ACTIVE,
        ]);
        return $model;
    }

    public function store($params)
    {
        return $this->setScenario(self::SCENARIO_CREATE)->saveModel($params);
    }

    /**
     * 条件必须前置
     * @return AdminTradeAccount|\Illuminate\Database\Eloquent\Builder
     */
    public function active()
    {
        return $this->whereStatus(self::STATUS_ACTIVE);
    }
}
