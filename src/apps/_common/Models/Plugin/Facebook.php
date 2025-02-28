<?php

namespace Common\Models\Plugin;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Action\ActionLog
 *
 * @property int $action_log_id 记录ID
 * @property int $user_id 用户ID
 * @property string $name 事件名称
 * @property string|null $content 事件附加信息
 * @property string $ip 用户访问IP
 * @property int $quality 用户质量
 * @property string|null $device_uuid 设备ID
 * @property string $app_version App版本
 * @property string $client_id 设备类型
 * @property int $created_time 创建时间
 * @property string $unit_type 设备型号
 * @property string $brand 设备品牌
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog whereActionLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog whereAppVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog whereCreatedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog whereDeviceUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog whereQuality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog whereUnitType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog orderByCustom($column = null, $direction = 'asc')
 * @property string $created_at 创建时间
 * @property string|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog whereUpdatedAt($value)
 * @property int $app_id app_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog whereAppId($value)
 * @property int $id
 * @property int $merchant_id merchant_id
 * @property string|null $apply_id
 * @property string|null $type
 * @property string|null $email
 * @property string|null $first_name
 * @property string|null $data_id
 * @property string|null $last_name
 * @property string|null $name_format
 * @property string|null $short_name
 * @property string|null $picture
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereApplyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereDataId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereNameFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook wherePicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereShortName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Facebook whereType($value)
 */
class Facebook extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    public $table = 'user_third_data_facebook';

    /**
     * @var bool
     */
    public $timestamps = false;

    protected $primaryKey = 'id';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $dates = [];

    protected static function boot()
    {
        parent::boot();

        static::setAppIdBootScope();
    }

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [

        ];
    }

    /**
     * 显示场景过滤
     * @return array
     */
    public function texts()
    {
        return [
        ];
    }

    /**
     * 显示非过滤
     * @return array
     */
    public function unTexts()
    {
        return [
        ];
    }

    /**
     * 显示规则
     * @return array
     */
    public function textRules()
    {
        return [
        ];
    }

}
