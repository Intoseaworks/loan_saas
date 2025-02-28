<?php

namespace Risk\Common\Models\Business\BankCard;

use Risk\Common\Models\Business\BusinessBaseModel;
use Risk\Common\Models\Business\User\User;

/**
 * Risk\Common\Models\Business\BankCard\BankCardPeso
 *
 * @property int $id
 * @property int|null $app_id merchant_id
 * @property int|null $business_app_id app_id
 * @property int|null $user_id
 * @property string|null $payment_type 支付方式类型
 * @property string|null $account_name 账户名
 * @property string|null $account_no 账户号码
 * @property string|null $bank_name 银行名
 * @property string|null $instituion_name 机构名称
 * @property string|null $channel 频道
 * @property int|null $status 状态:1正常 0废弃 -1待鉴权绑卡 -2系统清理(放款失败解绑银行卡)
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property string|null $sync_time
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso query()
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereAccountNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereBusinessAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereInstituionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankCardPeso whereUserId($value)
 * @mixin \Eloquent
 */
class BankCardPeso extends BusinessBaseModel
{
    /** 状态：正常 */
    const STATUS_ACTIVE = 1;
    /** 状态：废弃 */
    const STATUS_DELETE = 0;
    /** 状态 */
    const STATUS = [
        self::STATUS_ACTIVE => '正常',
        self::STATUS_DELETE => '废弃',
    ];

    public static $validate = [
        'data' => 'required|array|dyadic_array_field_value_contain:status,1',
        'data.*.id' => 'required|numeric',   // 业务系统银行卡ID，如记录自增id
        'data.*.account_no' => 'required|string',   // 银行卡号
    ];
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'data_bank_card_peso';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [
        'id',
        'app_id',
        'business_app_id',
        'user_id',
        'payment_type',
        'account_name',
        'account_no',
        'bank_name',
        'instituion_name',
        'channel',
        'status',
        'created_at',
        'updated_at',
    ];

    protected static function boot()
    {
        parent::boot();
        static::setMerchantIdBootScope();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function itemFormat($item)
    {
        return $this->format($item);
    }

    public function format($data)
    {
        return $data;
    }
}
