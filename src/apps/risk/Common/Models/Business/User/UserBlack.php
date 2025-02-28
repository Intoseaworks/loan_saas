<?php

namespace Risk\Common\Models\Business\User;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\Business\User\UserBlack
 *
 * @property int $id
 * @property int $app_id 商户ID
 * @property string $telephone
 * @property string $hit_value 命中黑名单值
 * @property string|null $hit_type 命中黑名单类型
 * @property string $remark
 * @property int $status 状态
 * @property string $type 禁用类型
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $black_time 黑名单生效时间
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlack newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlack newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlack query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlack whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlack whereBlackTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlack whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlack whereHitType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlack whereHitValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlack whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlack whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlack whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlack whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlack whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlack whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UserBlack extends RiskBaseModel
{
    const STATUS_NORMAL = 1;
    const STATUS_INVALID = 2;

    const TPYE_CANNOT_LOGIN = 'cannot_login';
    const TPYE_CANNOT_REGISTER = 'cannot_register';
    const TPYE_CANNOT_LOAN = 'cannot_loan';
    const TPYE_CANNOT_AUTH = 'cannot_auth';
    const TPYE = [
        self::TPYE_CANNOT_LOGIN => '限制登录',
        self::TPYE_CANNOT_REGISTER => '限制注册',
        self::TPYE_CANNOT_LOAN => '限制借款',
        self::TPYE_CANNOT_AUTH => '限制认证',
    ];

    /**
     * @var string
     */
    protected $table = 'user_black';
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

    public function user($class = User::class)
    {
        return $this->hasOne($class, 'telephone', 'telephone');
    }
}
