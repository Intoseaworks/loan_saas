<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/23
 * Time: 16:08
 */

namespace Common\Models\Login;


use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Login\UserLoginLog
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $login_type 登录方式
 * @property string $ip 登录ip
 * @property string|null $address
 * @property string|null $device 设备名称
 * @property string|null $browser 浏览器
 * @property string|null $platform 操作系统
 * @property string|null $language 语言
 * @property string|null $device_type 设备类型 tablet平板 mobile便捷设备 robot爬虫机器人 desktop桌面设备
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\UserLoginLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\UserLoginLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\UserLoginLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\UserLoginLog whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\UserLoginLog whereBrowser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\UserLoginLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\UserLoginLog whereDevice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\UserLoginLog whereDeviceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\UserLoginLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\UserLoginLog whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\UserLoginLog whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\UserLoginLog whereLoginType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\UserLoginLog wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\UserLoginLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\UserLoginLog whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\UserLoginLog orderByCustom($column = null, $direction = 'asc')
 * @property int $app_id app_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Login\UserLoginLog whereAppId($value)
 */
class UserLoginLog extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'user_login_log';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [
    ];

    protected static function boot()
    {
        parent::boot();

        //static::setAppIdBootScope();
    }
}
