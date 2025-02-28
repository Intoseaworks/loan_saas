<?php

namespace Common\Models\User;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

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
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|UserWhitelist whereValue($value)
 */
class UserWhitelist extends Model {

    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'user_whitelist';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $hidden = [];

    public function inWhiteMenu($value, $type) {
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

}
