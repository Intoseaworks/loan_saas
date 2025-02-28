<?php

namespace Common\Models\Config;

use Common\Traits\Model\StaticModel;
use Common\Utils\Host\HostHelper;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Config\ConfigLog
 *
 * @property int $id
 * @property int $merchant_id merchant_id
 * @property int $staff_id 员工id
 * @property string $ip 操作ip
 * @property string|null $key 配置key
 * @property string|null $old_config 旧配置
 * @property string $new_config 新配置
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigLog orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigLog whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigLog whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigLog whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigLog whereNewConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigLog whereOldConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigLog whereStaffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConfigLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ConfigLog extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'config_log';

    const SCENARIO_UPDATE_CONFIG = 'update_config';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [
            self::SCENARIO_UPDATE_CONFIG => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'staff_id' => LoginHelper::getAdminId(),
                'ip' => HostHelper::getIp(),
                'old_config',
                'new_config',
                'key'
            ]
        ];
    }

    public function add($key, $oldConfig, $newConfig)
    {
        if ($oldConfig == $newConfig) {
            return true;
        }
        $data = [
            'key' => $key,
            'old_config' => is_array($oldConfig) ? json_encode($oldConfig, 256) : $oldConfig,
            'new_config' => is_array($newConfig) ? json_encode($newConfig, 256) : $newConfig,
        ];
        return static::model()->setScenario(self::SCENARIO_UPDATE_CONFIG)->saveModel($data);
    }

}
