<?php

namespace Common\Services\Rbac\Models;

use Common\Traits\Model\GlobalScopeModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Services\Rbac\Models\RbacConfig
 *
 * @property int $id
 * @property int $merchant_id
 * @property string $project 项目名称
 * @property string $guard_name 模块
 * @property string $config 配置信息
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\RbacConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\RbacConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\RbacConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\RbacConfig whereConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\RbacConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\RbacConfig whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\RbacConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\RbacConfig whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\RbacConfig whereProject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\RbacConfig whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RbacConfig extends Model
{
    use GlobalScopeModel;

    /**
     * @var array
     */
    public $guarded = ['*'];

    /**
     * @var string
     */
    protected $table = 'rbac_configs';

    /**
     * @var array
     */
    protected $fillable = ['project', 'guard_name', 'config', 'created_at', 'updated_at', 'merchant_id'];

    protected static function boot()
    {
        parent::boot();
        static::setMerchantIdBootScope();
    }

    /**
     * @param $project
     * @param $guardName
     * @return array|mixed
     */
    public static function getProjectConfig($project, $guardName)
    {
        $config = static::where('project', $project)
            ->where('guard_name', $guardName)
            ->first();
        if ($config) {
            return json_decode($config->config, true);
        }

        return [];
    }

    /**
     * @param $project
     * @return array
     */
    public static function getGuardList($project)
    {
        $config = static::where('project', $project)
            ->get()
            ->toArray();

        $data = [];
        $i = 1;
        foreach ($config as $item) {
            $data[] = [
                'id' => $i,
                'key' => $item['guard_name'],
                'name' => json_decode($item['config'], true)['name'] ?? '',
            ];
            $i++;
        }

        return $data;
    }

    /**
     * @return array
     */
    public static function getAllSubProjectConnection()
    {
        $configs = static::get()->toArray();
        $connections = [];
        $masterConnection = null;
        foreach ($configs as $config) {
            $temp = json_decode($config['config'], true);
            if (!empty($temp['master'])) {
                //兼容测试环境
                $masterConnection[] = $temp['connection'];
                continue;
            }
            array_push($connections, $temp['connection']);
        }

        return array_unique(array_diff($connections, $masterConnection));
    }
}
