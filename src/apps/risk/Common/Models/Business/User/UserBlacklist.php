<?php

namespace Risk\Common\Models\Business\User;

use Illuminate\Support\Facades\DB;
use Risk\Common\Models\Business\BusinessBaseModel;

/**
 * Common\Models\User\UserBlackMenu
 *
 * @property int $id
 * @property string $type
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlackMenu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlackMenu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlackMenu query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlackMenu whereBlackTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlackMenu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlackMenu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlackMenu whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlackMenu whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlackMenu whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlackMenu whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlackMenu whereMerchantId($value)
 * @mixin \Eloquent
 * @property string $type 禁用类型
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlackMenu whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlackMenu orderByCustom($column = null, $direction = 'asc')
 * @property int $merchant_id 商户ID
 * @property string|null $from 来源
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlacklist whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlacklist whereValue($value)
 */
class UserBlacklist extends BusinessBaseModel {

    const TPYE_BANKCARD = "bankCard"; //银行卡
    const TPYE_PAN_CARD = "panCard"; //Pan卡
    const TPYE_EMAIL = "email"; //email
    const TPYE_TELEPHONE = "telephone"; //电话
    const TPYE_NAME_BIRTH = "nameBirth"; //名称与生日组合
    const TPYE_ADAAHAAR_NUMBER = "adaahaarNumber"; //A卡
    const TPYE_DEVICE_ID = "deviceId"; //硬件

    /**
     * @var string
     */
    protected $table = 'user_blacklist';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $hidden = [];

    public function inBlackMenu($value, $type) {
        if ($value) {
            $res = $this->where(["type" => $type, "value" => $value])->exists();
            if ($res) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function inBlackMenuUseMD5($value, $type) {
        if ($value) {
            $res = $this->where("type", $type)->where(DB::raw("MD5(value)"), $value)->exists();
            if ($res) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

}
