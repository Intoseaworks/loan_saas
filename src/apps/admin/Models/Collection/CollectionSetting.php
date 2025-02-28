<?php

namespace Admin\Models\Collection;

use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;

/**
 * Admin\Models\Collection\CollectionSetting
 *
 * @property int $id
 * @property string $key 名称
 * @property string $value 值
 * @property int $status 配置状态
 * @property string $remark 备注
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionSetting orderByCustom($column = null, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionSetting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionSetting whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionSetting whereValue($value)
 * @mixin \Eloquent
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionSetting whereMerchantId($value)
 * @property int|null $admin_id 操作人员id
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionSetting whereAdminId($value)
 */
class CollectionSetting extends \Common\Models\Collection\CollectionSetting
{

    const SCENARIO_LIST = 'list';
    const SCENARIO_CREATE = 'create';
    const SCENARIO_DELETE = 'delete';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'admin_id' => LoginHelper::getAdminId(),
                'key',
                'value',
                'status' => self::STATUS_NORMAL,
                'remark',
            ],
            self::SCENARIO_DELETE => [
                'status',
            ],
        ];
    }

    public function getList($param)
    {
        return $this->paginate();
    }

    /**
     * 根据id获取
     * @param $id
     * @return mixed
     */
    public function getOne($id)
    {
        return self::where('id', '=', $id)->first();
    }

    public function create($data)
    {
        return self::model(self::SCENARIO_CREATE)->saveModel($data);
    }

    /**
     * @param $key
     * @param $value
     * @param bool $cover
     * @return CollectionSetting|bool
     */
    public function updateSetting($key, $value, $cover = true)
    {
        if (!$cover) {
            $this->resetStatus($key);
        }
        $model = self::getOneByKey($key);
        if (!$model) {
            $model = new CollectionSetting();
            $model->key = $key;
        }
        return $model->setScenario(self::SCENARIO_CREATE)->saveModel([
            'value' => json_encode($value, 256)
        ]);
    }

    /**
     * @param $key
     */
    public function resetStatus($key)
    {
        $model = self::model()->where('key', $key)
            ->where('status', self::STATUS_NORMAL)->first();
        if ($model) {
            $model->setScenario(self::SCENARIO_DELETE)
                ->saveModel(['status' => self::STATUS_DELETE]);
        }
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getOneByKey($key)
    {
        return self::where('key', $key)->where('status', self::STATUS_NORMAL)->first();
    }

}
