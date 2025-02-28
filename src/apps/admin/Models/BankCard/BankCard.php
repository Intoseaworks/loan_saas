<?php

namespace Admin\Models\BankCard;

/**
 * Admin\Models\BankCard\BankCard
 *
 * @property int $id 银行卡id
 * @property int $user_id 用户id
 * @property string $no 银行卡号
 * @property string $name 银行卡户主姓名
 * @property string|null $bank 银行缩写
 * @property string $bank_name 开户行
 * @property string $reserved_telephone 银行预留手机号
 * @property int $status 状态:1正常 0废弃
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property string|null $bank_branch_name 所属支行
 * @property string|null $province_code 省级code
 * @property string|null $city_code
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereBankBranchName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereCityCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereProvinceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereReservedTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\BankCard\BankCard orderByCustom($column = null, $direction = 'asc')
 * @property-read \Common\Models\User\User $user
 * @property string|null $ifsc 印度ifsc_code值
 * @property string|null $city 所属城市
 * @property string $province 所属省
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereIfsc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereProvince($value)
 * @property int|null $merchant_id merchant_id
 * @property int $app_id app_id
 * @property string|null $reject_time
 * @property int|null $reject_day
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereRejectDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\BankCard\BankCard whereRejectTime($value)
 */
class BankCard extends \Common\Models\BankCard\BankCard
{
    const SCENARIO_LIST = 'list';
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_DELETE = 'delete';

    public function safes()
    {
        return [
        ];
    }

    public function texts()
    {
        return [
        ];
    }

    public function textRules()
    {
        return [
            'time' => [
            ],
            'array' => [
                'status' => self::STATUS,
            ],
        ];
    }

}
