<?php

namespace Api\Models\Action;

use Common\Utils\Data\DateHelper;

/**
 * Api\Models\Action\ActionLog
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
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog whereActionLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog whereAppVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog whereCreatedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog whereDeviceUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog whereQuality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog whereUnitType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Action\ActionLog orderByCustom($column = null, $direction = 'asc')
 * @property string $created_at 创建时间
 * @property string|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog whereUpdatedAt($value)
 * @property int $app_id app_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Action\ActionLog whereAppId($value)
 */
class ActionLog extends \Common\Models\Action\ActionLog
{

    const SCENARIO_CREATE = 'create';

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'app_id',
                'user_id',
                'name',
                'content',
                'ip',
                'quality',
                'device_uuid',
                'app_version',
                'client_id',
                'created_at',
                'updated_at' => DateHelper::dateTime(),
                'unit_type',
                'brand',
            ],
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

    public function create($data)
    {
        return $this->setScenario(self::SCENARIO_CREATE)->saveModel($data);
    }

}
