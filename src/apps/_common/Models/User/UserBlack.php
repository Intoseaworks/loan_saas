<?php

namespace Common\Models\User;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\User\UserBlack
 *
 * @property int $id
 * @property string $telephone
 * @property string $remark
 * @property int $status 状态
 * @property int $black_time 黑名单生效时间
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlack newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlack newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlack query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlack whereBlackTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlack whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlack whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlack whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlack whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlack whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlack whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlack whereMerchantId($value)
 * @mixin \Eloquent
 * @property string $type 禁用类型
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlack whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserBlack orderByCustom($column = null, $direction = 'asc')
 * @property int $merchant_id 商户ID
 * @property string $hit_value 命中黑名单值
 * @property string|null $hit_type 命中黑名单类型
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlack whereHitType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlack whereHitValue($value)
 * @property string|null $expire_time 黑名单失效时间
 * @method static \Illuminate\Database\Eloquent\Builder|UserBlack whereExpireTime($value)
 */
class UserBlack extends Model
{

    const STATUS_NORMAL = 1;
    const STATUS_INVALID = 2;

    const TPYE_CANNOT_LOGIN = 'cannot_login';
    const TPYE_CANNOT_REGISTER= 'cannot_register';
    const TPYE_CANNOT_LOAN = 'cannot_loan';
    const TPYE_CANNOT_AUTH = 'cannot_auth';
    const TPYE = [
        self::TPYE_CANNOT_LOGIN => '限制登录',
        self::TPYE_CANNOT_REGISTER => '限制注册',
        self::TPYE_CANNOT_LOAN => '限制借款',
//        self::TPYE_CANNOT_AUTH => '限制认证',
    ];

    use StaticModel;

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

    /**
     * 手机号能否登录
     * @param $telephone
     * @return bool
     */
    public function canLogin($telephone)
    {
        return !$this->isActive()->whereTelephone($telephone)->whereType(self::TPYE_CANNOT_LOGIN)->exists();
    }

    /**
     * 手机号能否注册
     * @param $telephone
     * @return bool
     */
    public function canRegister($telephone)
    {
        return !$this->isActive()->withoutGlobalScope(self::$bootScopeMerchant)->whereTelephone($telephone)->whereType(self::TPYE_CANNOT_REGISTER)->exists();
    }

    /**
     * 是否不能认证
     *
     * @param $telephone
     * @return bool
     */
    public function isCannotAuth($telephone)
    {
        return $this->isActive()->whereTelephone($telephone)->whereType(self::TPYE_CANNOT_AUTH)->exists();
    }

    /**
     * 手机号能否借款
     * @param $telephone
     * @return bool
     */
    public function canLoan($telephone)
    {
        return !$this->isActive()->whereTelephone($telephone)->whereType(self::TPYE_CANNOT_LOAN)->exists();
    }

    /**
     * 判断是否符合黑名单 条件必须前置
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function isActive()
    {
        return $this->whereStatus(self::STATUS_NORMAL)->where('black_time', '<', $this->getDate())->where('expire_time', '>', $this->getDate());
    }
}
