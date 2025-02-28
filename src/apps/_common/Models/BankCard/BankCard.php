<?php

namespace Common\Models\BankCard;

use Carbon\Carbon;
use Common\Models\User\User;
use Common\Traits\Model\StaticModel;
use Common\Utils\Data\DateHelper;
use Common\Utils\Data\DistrictCodeHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\BankCard\BankCard
 *
 * @property int $id 银行卡id
 * @property int $user_id 用户id
 * @property string $no 银行卡号
 * @property string|null $bank 银行缩写
 * @property string $bank_name 开户行
 * @property string|null $bank_branch_name 所属支行
 * @property string $reserved_telephone 银行预留手机号
 * @property int|null $is_default 是否是默认银行卡，默认是0
 * @property string|null $url 图片路径
 * @property int $status 状态:1正常 0废弃
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereBankBranchName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereReservedTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereUserId($value)
 * @mixin \Eloquent
 * @property string|null $province_code 省级code
 * @property string|null $city_code
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereCityCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereProvinceCode($value)
 * @property string $name 银行卡户主姓名
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereName($value)
 * @property-read \Common\Models\User\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard orderByCustom($column = null, $direction = 'asc')
 * @property string|null $ifsc 印度ifsc_code值
 * @property string|null $city 所属城市
 * @property string $province 所属省
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereIfsc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereProvince($value)
 * @property int|null $merchant_id merchant_id
 * @property int $app_id app_id
 * @property string|null $reject_time
 * @property int|null $reject_day
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereRejectDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard whereRejectTime($value)
 */
class BankCard extends Model
{
    use StaticModel;

    const SCENARIO_LIST = 'list';
    const SCENARIO_DETAIL = 'detail';

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

    /**
     * @var string
     */
    protected $table = 'bank_card';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];

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

        static::setAppIdBootScope();
    }

    public function texts()
    {
        return [
            self::SCENARIO_DETAIL => [
                'no',
                'bank',
                'name',
                'bank_name',
                'bank_branch_name',
                'reserved_telephone',
                'is_default',
                'created_at',
                'updated_at',
                'city_code',
                'province_code',
                'status',
                'ifsc',
                'city',
                'province',
            ],
            self::SCENARIO_LIST => [
                'no',
                'bank',
                'bank_name',
                'bank_branch_name',
                'reserved_telephone',
                'is_default',
                'created_at',
                'updated_at',
                'city_code',
                'province_code',
                'status',
                'ifsc',
                'city',
                'province',
            ]
        ];
    }

    public function textRules()
    {
        return [
            'function' => [
                'city_code' => function ($model) {
                    return DistrictCodeHelper::getByCode($model->city_code)['name'] ?? '';
                },
                'province_code' => function ($model) {
                    return DistrictCodeHelper::getByCode($model->province_code)['name'] ?? '';
                }
            ],
        ];
    }

    /**
     * 根据 user_id 和 no 获取model
     * @param $userId
     * @param $bankCardNo
     * @param $where
     * @return \Illuminate\Database\Eloquent\Builder|Model|null|object|static
     */
    public static function getByUserIdAndNo($userId, $bankCardNo, $where = [])
    {
        return self::query()->where($where)->where([
            'user_id' => $userId,
            'no' => $bankCardNo,
        ])->first();
    }

    /**
     * 根据 user_id 和 no 获取可用银行卡
     * @param $userId
     * @param $bankCardNo
     * @return Builder|Model|null|object
     */
    public static function getUsableByUserIdAndNo($userId, $bankCardNo)
    {
        return self::query()->where([
            'user_id' => $userId,
            'no' => $bankCardNo,
        ])->whereIn('status', [
            self::STATUS_ACTIVE,
            self::STATUS_DELETE,
            self::STATUS_SYSTEM_CLEAR
        ])->first();
    }

    public function encodeUserIdentifying()
    {
        return $this->reserved_telephone . '_' . $this->user_id;
    }

    public function decodeUserIdentifying($userIdentifying)
    {
        list($phone, $userId) = explode('_', $userIdentifying);

        return [
            'user_id' => $userId,
            'telephone' => $phone,
        ];
    }

    public static function getWaitAuthBankcard($userId, $bankCardNo)
    {
        return self::query()->withoutGlobalScope('wait_auth')
            ->where([
                'user_id' => $userId,
                'no' => $bankCardNo,
                'status' => self::STATUS_WAIT_AUTH
            ])->first();
    }

    /**
     * 删除用户指定状态银行卡信息
     * @param $userId
     * @param int $status
     * @return mixed
     */
    public static function clearStatus($userId, $status = self::STATUS_ACTIVE)
    {
        $where = [
            'user_id' => $userId,
            'status' => $status,
        ];

        if ($status == self::STATUS_ACTIVE) {
            $toStatus = self::STATUS_DELETE;
        } elseif ($status == self::STATUS_WAIT_AUTH) {
            $toStatus = self::STATUS_BIND_FAILED;
        } else {
            return false;
        }

        return self::model()->withoutGlobalScope('wait_auth')
            ->where($where)
            ->update(['status' => $toStatus]);
    }

    /**
     * 系统清理用户指定状态银行卡信息
     * @param $userId
     * @param int $status
     * @return mixed
     */
    public static function clearStatusSystem($userId, $status = self::STATUS_ACTIVE)
    {
        $where = [
            'user_id' => $userId,
            'status' => $status,
        ];
        return BankCard::model()->where($where)->update(['status' => BankCard::STATUS_SYSTEM_CLEAR]);
    }

    public static function clearSameNo($userId, $no, $exceptId = [])
    {
        $where = [
            'user_id' => $userId,
            'no' => $no,
        ];
        return BankCard::model()->where($where)
            ->whereNotIn('id', (array)$exceptId)
            ->delete();
    }

    public function updateToAuthSuccess()
    {
        self::clearStatus($this->user_id);
        self::clearSameNo($this->user_id, $this->no, $this->id);

        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }

    public function updateToDelete()
    {
        $this->status = self::STATUS_DELETE;
        return $this->update();
    }

    public function isRejecting($userId)
    {
        $bankcard = $this->getAllBankCardHistoryByUserId($userId)->where('reject_day', '!=', 0)->first();

        if ($bankcard && Carbon::parse($bankcard->reject_time)->addDay($bankcard->reject_day)->isFuture()) {
            return true;
        }
        return false;
    }

    /**
     * 获取总拒绝次数
     * @param $userId
     * @param int $days
     * @return int
     */
    public function rejectCount($userId, int $days = 90)
    {
        $where = [
            ['user_id', $userId],
            ['reject_time', '>', date('Y-m-d H:i:s', strtotime("-{$days} days"))]
        ];

        return $this->getAllBankCardHistoryByUserId($userId, $where)
            ->count();
    }

    /**
     * 更新为拒绝
     * @param int $days
     * @return bool
     */
    public function updateIsReject($days = 0)
    {
        $this->reject_time = DateHelper::dateTime();
        $this->reject_day = $days;
        $this->status = self::STATUS_BIND_FAILED;
        return $this->save();
    }

    /**
     * 去除全局作用域获取bankcard
     * @param $userId
     * @param array $where
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllBankCardHistoryByUserId($userId, $where = [])
    {
        $query = self::query()->withoutGlobalScopes()
            ->where('user_id', $userId)
            ->where($where);
        return $query->orderByDesc('id')->get();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
