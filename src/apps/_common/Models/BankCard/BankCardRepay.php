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
class BankCardRepay extends Model
{
    use StaticModel;

    const SCENARIO_LIST = 'list';
    const SCENARIO_DETAIL = 'detail';

    /** 状态：正常 */
    const STATUS_ACTIVE = 1;
    /** 状态：废弃 */
    const STATUS_DELETE = 2;
    /** 状态 */
    const STATUS = [
        self::STATUS_ACTIVE => '正常',
        self::STATUS_DELETE => '废弃',
    ];

    /** @var array 绑定成功过的状态，生效过 */
    const STATUS_ONCE_ACTIVE = [
        self::STATUS_ACTIVE,
        self::STATUS_DELETE,
    ];

    /**
     * @var string
     */
    protected $table = 'bank_card_repay';
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
    }

    public function texts()
    {
        return [];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
