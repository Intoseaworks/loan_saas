<?php

namespace Common\Models\User;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\User\UserAuthBlack
 *
 * @property int $id
 * @property string $telephone
 * @property string $remark
 * @property string $type 类型
 * @property int $status 状态
 * @property int $black_time 黑名单生效时间
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuthBlack newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuthBlack newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuthBlack query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuthBlack whereBlackTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuthBlack whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuthBlack whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuthBlack whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuthBlack whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuthBlack whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuthBlack whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuthBlack whereMerchantId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuthBlack whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuthBlack orderByCustom($column = null, $direction = 'asc')
 * @property int $merchant_id 商户ID
 * @property int $user_id 用户id
 * @property string $receive
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuthBlack whereReceive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuthBlack whereUserId($value)
 */
class UserAuthBlack extends Model
{

    const STATUS_NORMAL = 1;
    const STATUS_INVALID = 2;

    const TPYE_AADHAAR = 'aadhaar';

    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'user_auth_black';
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

        static::setMerchantIdBootScope();
    }

    public function textRules()
    {
        return [];
    }

}
