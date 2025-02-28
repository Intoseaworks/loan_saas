<?php

namespace Common\Models\Staff;

use Common\Models\Merchant\Merchant;
use Common\Services\Rbac\Traits\HasRoles;
use Common\Traits\Model\StaticModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;


/**
 * Common\Models\Staff
 *
 * @property int                             $id
 * @property string                          $username
 * @property string                          $nickname
 * @property int                             $last_login_time
 * @property string                          $role
 * @property int                             $company_id
 * @property string                          $last_login_ip
 * @property \Carbon\Carbon|null             $created_at
 * @property \Carbon\Carbon|null             $updated_at
 * @property-read mixed                      $login_type_text
 * @property-read \Common\Models\Staff\Staff $staff
 * @mixin \Eloquent
 * @property string $password_hash 密码
 * @property string|null $tissue 组织
 * @property string|null $uuid 用户唯一标示
 * @property string|null $ding_openid 钉钉openid
 * @property string|null $wechat_openid 微信openid
 * @property string|null $remember_token
 * @property string|null $email email
 * @property int $status 状态
 * @property int $last_update_pwd_time 上次更新密码
 * @property int $is_need_update_pwd 需要更新密码
 * @property int $is_need_ding_login 是否需要钉钉登录，1:需要，0:不需要
 * @property string $ding_unionid 钉钉unionid
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Services\Rbac\Models\Permission[] $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Services\Rbac\Models\Role[] $roles
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff role($roles)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereDingOpenid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereDingUnionid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereIsNeedDingLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereIsNeedUpdatePwd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereLastLoginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereLastUpdatePwdTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff wherePasswordHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereTissue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereWechatOpenid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff orderByCustom($column = null, $direction = 'asc')
 * @property string $last_login_time_old 最后登录时间
 * @property int $created_at_old
 * @property int $updated_at_old
 * @property int $last_update_pwd_time_old 上次更新密码
 * @property string|null $password 密码
 * @property string|null $deleted_at 删除时间
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Common\Models\Staff\Staff onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereCreatedAtOld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereLastLoginTimeOld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereLastUpdatePwdTimeOld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereUpdatedAtOld($value)
 * @method static \Illuminate\Database\Query\Builder|\Common\Models\Staff\Staff withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Common\Models\Staff\Staff withoutTrashed()
 * @property int $merchant_id merchant_id
 * @property int $is_super 是否超级管理员账号
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereIsSuper($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff whereMerchantId($value)
 * @property int|null $show_chinese 1=显示中文;0=不显示中文
 * @property-read int|null $permissions_count
 * @property-read int|null $roles_count
 * @method static Builder|Staff whereShowChinese($value)
 */
class Staff extends Model implements JWTSubject, AuthenticatableContract, AuthorizableContract
{
    use StaticModel,HasRoles;
    use Authenticatable, Authorizable;
    use SoftDeletes;
    use HasRoles;
    public $timestamps = false;

    /** @var int 正常 */
    const STATUS_NORMAL = 1;
    /** @var int 禁用 */
    const STATUS_DISABLE = 2;
    /** @var int 删除 */
    const STATUS_DELETE = -1;
    /** @var array status */
    const STATUS = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLE => '禁用',
        self::STATUS_DELETE => '删除',
    ];

    /** @var int 密码已更新或无需更新 */
    const IS_NEED_UPDATE_PWD_NONEED = 0;
    /** @var int 密码需要更新 */
    const IS_NEED_UPDATE_PWD_NEED = 1;
    const IS_NEED_UPDATE_PWD = [
        self::IS_NEED_UPDATE_PWD_NONEED => '不需更新',
        self::IS_NEED_UPDATE_PWD_NEED => '需更新'
    ];
    /** 密码重置间隔 30天修改一次 */
    const PASSWORD_EXPIRE_TIME = 30 * 24 * 3600;

    /** 是否支持多地登录 */
    const IS_ANY_LOGIN_SUPPORT = 1;
    const IS_ANY_LOGIN_NONSUPPORT = 0;
    const IS_ANY_LOGIN = [
        self::IS_ANY_LOGIN_SUPPORT => '支持多地登录',
        self::IS_ANY_LOGIN_NONSUPPORT => '不支持多地登录',
    ];

    /** @var int 不需要钉钉登录 */
    const IS_NEED_DING_LOGIN_NONEED = 0;
    /** @var int 需要钉钉登录 */
    const IS_NEED_DING_LOGIN_NEED = 1;
    const IS_NEED_DING = [
        self::IS_NEED_DING_LOGIN_NONEED => '不需要钉钉登录',
        self::IS_NEED_DING_LOGIN_NEED => '需要钉钉登录'
    ];

    /** @var int 是否超管账号：是 */
    const IS_SUPER_YES = 1;
    /** @var int 是否超管账号：否 */
    const IS_SUPER_NO = 0;

    /**
     * @var string
     */
    protected $table = 'staff';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = ['password', 'password_hash'];

    /**
     * laravel-permission插件需要
     *
     * @var string
     */
    public $guard_name = 'admin';

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('valid', function (Builder $builder) {
            $builder->where('status', '!=', self::STATUS_DELETE);
        });

        static::setMerchantIdBootScope();
    }

    /**
     *
     * @param \DateTime|int $value
     * @return false|int|string
     */
    public function fromDateTime($value)
    {
        return strtotime((string)parent::fromDateTime($value));
    }

    public static function passwordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);
    }

    public function getOneByData($data)
    {
        return self::where($data)->first();
    }

    public function updPassword($password)
    {
        $this->password = self::passwordHash(trim($password));
        return $this->save();
    }

    public function canLogin()
    {
        return $this->status == static::STATUS_NORMAL && optional($this->merchant)->status == Merchant::STATUS_NORMAL;
    }

    /**
     * 过滤禁用用户
     *
     * @return Staff
     */
    public function isActive()
    {
        return $this->where('status', static::STATUS_NORMAL);
    }

    public function merchant($class = Merchant::class)
    {
        return $this->belongsTo($class, 'merchant_id', 'id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            self::$relationMerchantField => $this->merchant_id,
        ];
    }

}
