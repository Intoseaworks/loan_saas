<?php

namespace Common\Models\Login;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Admin\Models\Login
 *
 * @property int                             $id
 * @property string                          $client_url client url
 * @property int                             $staff_id 用户id
 * @property int                             $login_type 登录方式
 * @property string                          $ip 登录ip
 * @property \Carbon\Carbon|null             $created_at
 * @property \Carbon\Carbon|null             $updated_at
 * @property-read mixed                      $login_type_text
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\LoginLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\LoginLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\LoginLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\LoginLog whereClientUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\LoginLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\LoginLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\LoginLog whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\LoginLog whereLoginType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\LoginLog whereStaffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\LoginLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\LoginLog orderByCustom($column = null, $direction = 'asc')
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\LoginLog whereMerchantId($value)
 */
class LoginLog extends Model
{
    use StaticModel;

    /** @var int 登录方式：账密 */
    const LOGIN_TYPE_PWD = 1;
    /** @var int  登录方式：钉钉扫码 */
    const LOGIN_TYPE_DING = 2;
    /** @var array 登录方式 */
    const LOGIN_TYPE = [
        self::LOGIN_TYPE_PWD => '账号密码登录',
        self::LOGIN_TYPE_DING => '钉钉扫码登录'
    ];
    /**
     * @var string
     */
    protected $table = 'login_log';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [
        'merchant_id',
        'client_url',
        'staff_id',
        'login_type',
        'ip',
    ];
    protected $appends = ['login_type_text'];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }
}
