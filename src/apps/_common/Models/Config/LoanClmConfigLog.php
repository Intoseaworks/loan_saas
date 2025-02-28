<?php

namespace Common\Models\Config;

use Common\Traits\Model\StaticModel;
use Common\Utils\Host\HostHelper;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Config\LoanClmConfigLog
 *
 * @property int $id
 * @property int $merchant_id merchant_id
 * @property int $staff_id staff_id
 * @property string $ip 操作ip
 * @property string|null $old_config 旧配置集合json
 * @property string|null $new_config 新配置集合json
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfigLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfigLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfigLog orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfigLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfigLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfigLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfigLog whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfigLog whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfigLog whereNewConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfigLog whereOldConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanClmConfigLog whereStaffId($value)
 * @mixin \Eloquent
 */
class LoanClmConfigLog extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'loan_clm_config_log';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

    protected $guarded = [];

    const SCENARIO_UPDATE_CONFIG = 'update_config';

    const UPDATED_AT = null;

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function safes()
    {
        return [
            self::SCENARIO_UPDATE_CONFIG => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'staff_id' => LoginHelper::getAdminId(),
                'ip' => HostHelper::getIp(),
                'old_config',
                'new_config',
            ],
        ];
    }

    public static function add(array $oldConfig, array $newConfig)
    {
        $oldConfigJson = json_encode($oldConfig);
        $newConfigJson = json_encode($newConfig);

        if ($oldConfigJson == $newConfigJson) {
            return true;
        }
        $data = [
            'old_config' => $oldConfigJson,
            'new_config' => $newConfigJson,
        ];
        return static::model(self::SCENARIO_UPDATE_CONFIG)->saveModel($data);
    }
}
