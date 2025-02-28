<?php

namespace Risk\Common\Models\Business\BankCard;

use Common\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Builder;
use Risk\Common\Models\Business\BusinessBaseModel;
use Risk\Common\Models\Business\User\User;
use Risk\Common\Models\Common\BankInfo;

/**
 * Risk\Common\Models\Business\BankCard\BankCard
 *
 * @property int $id 银行卡id
 * @property int|null $app_id merchant_id
 * @property int $business_app_id app_id
 * @property int $user_id 用户id
 * @property string $no 银行卡号
 * @property string $name 银行卡户主姓名
 * @property string|null $bank 银行缩写
 * @property string $bank_name 开户行
 * @property string $reserved_telephone 银行预留手机号
 * @property int $status 状态:1正常 0废弃 -1待鉴权绑卡 -2系统清理(放款失败解绑银行卡)
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property string|null $bank_branch_name 所属支行
 * @property string|null $province_code 省级code
 * @property string|null $city_code
 * @property string|null $ifsc
 * @property string|null $city
 * @property string|null $province
 * @property string|null $reject_time
 * @property int|null $reject_day
 * @property string|null $sync_time
 * @property-read User $user
 * @method static Builder|BankCard newModelQuery()
 * @method static Builder|BankCard newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static Builder|BankCard query()
 * @method static Builder|BankCard whereAppId($value)
 * @method static Builder|BankCard whereBank($value)
 * @method static Builder|BankCard whereBankBranchName($value)
 * @method static Builder|BankCard whereBankName($value)
 * @method static Builder|BankCard whereBusinessAppId($value)
 * @method static Builder|BankCard whereCity($value)
 * @method static Builder|BankCard whereCityCode($value)
 * @method static Builder|BankCard whereCreatedAt($value)
 * @method static Builder|BankCard whereId($value)
 * @method static Builder|BankCard whereIfsc($value)
 * @method static Builder|BankCard whereName($value)
 * @method static Builder|BankCard whereNo($value)
 * @method static Builder|BankCard whereProvince($value)
 * @method static Builder|BankCard whereProvinceCode($value)
 * @method static Builder|BankCard whereRejectDay($value)
 * @method static Builder|BankCard whereRejectTime($value)
 * @method static Builder|BankCard whereReservedTelephone($value)
 * @method static Builder|BankCard whereStatus($value)
 * @method static Builder|BankCard whereSyncTime($value)
 * @method static Builder|BankCard whereUpdatedAt($value)
 * @method static Builder|BankCard whereUserId($value)
 * @mixin \Eloquent
 */
class BankCard extends BusinessBaseModel
{
    /** 状态：正常 */
    const STATUS_ACTIVE = 1;
    /** 状态：废弃 */
    const STATUS_DELETE = 0;
    /** 状态：待鉴权绑卡 */
    const STATUS_WAIT_AUTH = -1;
    /** 状态：系统清理(放款失败解绑银行卡..) */
    const STATUS_SYSTEM_CLEAR = -2;
    /** 状态：-1 流转为未绑定成功 */
    const STATUS_BIND_FAILED = -3;
    /** 状态 */
    const STATUS = [
        self::STATUS_ACTIVE => '正常',
        self::STATUS_DELETE => '废弃',
        self::STATUS_WAIT_AUTH => '待认证',
        self::STATUS_SYSTEM_CLEAR => '系统解绑',
    ];

    /** @var array 绑定成功过的状态，生效过 */
    const STATUS_ONCE_ACTIVE = [
        self::STATUS_ACTIVE,
        self::STATUS_DELETE,
        self::STATUS_SYSTEM_CLEAR,
    ];
    public static $validate = [
        'data' => 'required|array|dyadic_array_field_value_contain:status,1',
        'data.*.id' => 'required|numeric',   // 业务系统银行卡ID，如记录自增id
        'data.*.no' => 'required|string',   // 银行卡号
        'data.*.name' => 'required|string', // 用户姓名
        'data.*.bank_name' => 'required|string',    // 银行名
        'data.*.ifsc' => 'required|string', // ifsc
        'data.*.status' => 'required|integer', // 状态。 1:正常  0:废弃   至少需要一条状态为 1 的银行卡记录
        'data.*.created_at' => 'required|date_format:Y-m-d H:i:s', // 创建时间

        'data.*.bank_branch_name' => 'string', // 支行名称
        'data.*.city' => 'string', // 城市
        'data.*.province' => 'string', // 州
    ];
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'data_bank_card';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [
        'id',
        'app_id',
        'business_app_id',
        'user_id',
        'no',
        'name',
        'bank',
        'bank_name',
        'reserved_telephone',
        'status',
        'created_at',
        'updated_at',
        'bank_branch_name',
        'province_code',
        'city_code',
        'ifsc',
        'city',
        'province',
        'reject_time',
        'reject_day',
    ];

    protected static function boot()
    {
        parent::boot();
        //此处约束名默认 age
        static::addGlobalScope('bind_failed', function (Builder $builder) {
            $builder->where('status', '!=', self::STATUS_BIND_FAILED);
        });
        static::addGlobalScope('wait_auth', function (Builder $builder) {
            $builder->where('status', '!=', self::STATUS_WAIT_AUTH);
        });

//        static::setAppIdBootScope();
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
        if (!isset($data['ifsc'])) {
            throw new ApiException('ifsc 不能为空');
        }

        # 判读ifsc是否有效
        $bankInfo = $this->checkIfsc($data['ifsc']);

        !isset($data['bank_name']) && $data['bank_name'] = $bankInfo->bank;
        !isset($data['bank_branch_name']) && $data['bank_branch_name'] = $bankInfo->branch;
        !isset($data['city']) && $data['city'] = $bankInfo->city;
        !isset($data['province']) && $data['province'] = $bankInfo->state;

        return $data;
    }

    public function checkIfsc($ifsc)
    {
        $bankInfo = BankInfo::query()->where('ifsc', $ifsc)->first();
        if (!$bankInfo) {
            throw new ApiException('ifsc不正确，请重新选择或填写');
        }
        return $bankInfo;
    }
}
