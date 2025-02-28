<?php

namespace Risk\Common\Models\Config;

use Common\Utils\MerchantHelper;
use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\Config\Config
 *
 * @property int $id
 * @property int $app_id app_id
 * @property string $key
 * @property string $value
 * @property string $remark
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Config newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Config newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Config query()
 * @method static \Illuminate\Database\Eloquent\Builder|Config whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Config whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Config whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Config whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Config whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Config whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Config whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Config whereValue($value)
 * @mixin \Eloquent
 */
class Config extends RiskBaseModel
{
    /*------------------公共start----------------------*/
    const STATUS_NORMAL = 1;
    const STATUS_FORGET = -1;
    /**
     * @var string
     */
    protected $table = 'config';//正常
    protected $guarded = [];//失效

    /**
     * 公共失效方法
     *
     * @param $key
     * @return bool
     */
    public static function forget($key)
    {
        $model = static::where('key', $key)->first();
        if ($model) {
            $model->status = self::STATUS_FORGET;
            $model->updated_at = date('Y-m-d H:i:s');
            $model->forget_at = date('Y-m-d H:i:s');
            $model->save();
        } else {
            return false;
        }
    }

    /**
     * 公共获取多个配置
     *
     * @param array $keys
     * @return mixed
     */
    public static function gets(array $keys)
    {
        $datas = static::whereIn('key', $keys)
            ->where('status', self::STATUS_NORMAL)
            ->pluck('value', 'key');
        foreach ($datas as $key => $val) {
            if (is_array(json_decode($val))) {
                $datas[$key] = (array)json_decode($val, true);
            }
        }
        return $datas;
    }

    public static function getKeys($name = null)
    {
        $configs = Config::CONFIG_ALIAS;
        !is_null($name) && $configs = array_only(Config::CONFIG_ALIAS, (array)$name);

        return array_keys($configs);
    }

    /**
     * 获取配置值必要方法 [值]
     * @param $key
     * @param bool $default
     * @param null $appId
     * @return array|bool|mixed
     * @throws \Exception
     */
    public static function getValueByKey($key, $default = false, $appId = null)
    {
        return self::get($key, $default, $appId);
    }

    /**
     * 公共获取方法
     * @param $key
     * @param bool $default
     * @param $appId
     * @return array|bool|mixed
     * @throws \Exception
     */
    public static function get($key, $default = false, $appId = null)
    {
        $appId = $appId ?? MerchantHelper::getMerchantId();

        if (!isset($appId)) {
            throw new \Exception('config 获取未定义appId');
        }

        $where = [
            'app_id' => $appId,
            'key' => $key,
            'status' => self::STATUS_NORMAL,
        ];
        $query = static::query()->where($where);
        if ($query->exists()) {
            $value = $query->value('value');
            if (is_array(json_decode($value, true))) {
                $value = (array)json_decode($value, true);
            }
            return $value;
        } else {
            return $default;
        }
    }

    public static function findOrCreate($appId, $key, $value)
    {
        if (self::get($key, false, $appId) !== false) {
            return true;
        }
        $remark = array_get(self::CONFIG_ALIAS, $key, '');
        return self::set($appId, $key, $value, $remark);
    }

    /**
     * 公共设置方法
     * @param $appId
     * @param $key
     * @param $value
     * @param string $remark
     * @return bool
     * @throws \Exception
     */
    public static function set($appId, $key, $value, $remark = '')
    {
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        $where = [
            'key' => $key,
            'app_id' => $appId
        ];
        $model = static::where($where)->first();
        if (!$model) {
            $model = new static();
            $model->app_id = $appId;
            $model->key = $key;
            $model->created_at = date('Y-m-d H:i:s');
        }
        $model->value = $value;
        $model->status = self::STATUS_NORMAL;
        $model->updated_at = date('Y-m-d H:i:s');
        $model->remark = $remark;

        return $model->save();
    }

    /**
     * 创建/更新配置必要方法
     * @param $key
     * @param $value
     * @param $merchantId
     * @return bool
     * @throws \Exception
     */
    public static function createOrUpdate($key, $value, $merchantId)
    {
        $remark = array_get(self::CONFIG_ALIAS, $key, '');
        return self::set($merchantId, $key, $value, $remark);
    }

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    /*------------------公共end----------------------*/
}
