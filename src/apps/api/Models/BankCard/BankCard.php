<?php

namespace Api\Models\BankCard;

use Common\Traits\Model\StaticModel;
use Common\Utils\MerchantHelper;

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
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\BankCard\BankCard whereCityCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\BankCard\BankCard whereProvinceCode($value)
 * @property string $name 银行卡户主姓名
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\BankCard\BankCard whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\BankCard\BankCard orderByCustom($column = null, $direction = 'asc')
 * @property-read \Common\Models\User\User $user
 * @property string|null $ifsc 印度ifsc_code值
 * @property string|null $city 所属城市
 * @property string $province 所属省
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\BankCard\BankCard whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\BankCard\BankCard whereIfsc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\BankCard\BankCard whereProvince($value)
 * @property int|null $merchant_id merchant_id
 * @property int $app_id app_id
 * @property string|null $reject_time
 * @property int|null $reject_day
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\BankCard\BankCard whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\BankCard\BankCard whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\BankCard\BankCard whereRejectDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\BankCard\BankCard whereRejectTime($value)
 */
class BankCard extends \Common\Models\BankCard\BankCard
{
    use StaticModel;

    const SCENARIO_DETAIL = 'detail';
    const SCENARIO_CREATE = 'create';
    const SCENARIO_HOME = 'home';

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

    /**
     * @return array
     */
    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'app_id' => MerchantHelper::getAppId(),
                'merchant_id' => MerchantHelper::getMerchantId(),
                'user_id',
                'no',
                'name',
                'bank_name',
                'bank',
                'bank_branch_name',
                'reserved_telephone',
                'status',
                'ifsc',
                'city',
                'province',
                'bank_account_type'
            ],
        ];
    }

    public function texts()
    {
        return [
            self::SCENARIO_DETAIL => [
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
            ],
            self::SCENARIO_HOME => [
                'no',
                'bank',
                'bank_name',
                'bank_branch_name',
                'reserved_telephone',
                'province_code',
                'city_code',
            ],
        ];
    }

    public function textRules()
    {
        return [];
    }

}
