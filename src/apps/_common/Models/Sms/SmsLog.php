<?php

namespace Common\Models\Sms;

use Common\Traits\Model\StaticModel;
use Common\Utils\Data\DateHelper;
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
 * @property string|null $app_code
 * @property string|null $area_code 区域
 * @property string|null $telephone
 * @property string|null $send_content 发送内容
 * @property int|null $type
 * @property string|null $remark
 * @method static \Illuminate\Database\Eloquent\Builder|SmsLog whereAppCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsLog whereAreaCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsLog whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsLog whereSendContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsLog whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsLog whereType($value)
 * @property string|null $event_id 事件ID
 * @property int|null $status 状态
 * @method static \Illuminate\Database\Eloquent\Builder|SmsLog whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsLog whereStatus($value)
 * @property string|null $res 返回
 * @method static \Illuminate\Database\Eloquent\Builder|SmsLog whereRes($value)
 */
class SmsLog extends Model
{

    const SCENARIO_CREATE = 'create';
    const TYPE_SMS = 'sms';
    const TYPE_IVR = 'ivr';

    use StaticModel;

    /**
     * @var string
     */
    public $table = 'sms_log';

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

    protected static function boot() {
        parent::boot();

        static::setAppIdBootScope();
    }

    /**
     * 安全属性
     * @return array
     */
    public function safes() {
        return [
            self::SCENARIO_CREATE => [
                "telephone",
                "event_id",
                "send_content",
                "type",
                "status",
                "remark",
                "created_at" => DateHelper::dateTime(),
                "res",
                "sms_channel",
                "msg_id",
            ],
        ];
    }

    /**
     * 显示场景过滤
     * @return array
     */
    public function texts() {
        return [
        ];
    }

    /**
     * 显示非过滤
     * @return array
     */
    public function unTexts() {
        return [
        ];
    }

    /**
     * 显示规则
     * @return array
     */
    public function textRules() {
        return [
        ];
    }

    public function create($data) {
        return $this->setScenario(self::SCENARIO_CREATE)->saveModel($data);
    }

}
