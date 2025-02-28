<?php

namespace Admin\Models\Collection;

/**
 * Admin\Models\Collection\CollectionFinish
 *
 * @property int $id 完结ID
 * @property int $collection_id 催收记录ID
 * @property int $order_id 催收记录ID
 * @property int $admin_id 案件催收员ID
 * @property string $from_status 完结前案子状态
 * @property string $to_status 完结后案子状态
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionFinish newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionFinish newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionFinish orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionFinish query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionFinish whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionFinish whereCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionFinish whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionFinish whereFromStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionFinish whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionFinish whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionFinish whereToStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionFinish whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionFinish whereMerchantId($value)
 * @property int $collection_assign_id 催收分单ID
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionFinish whereCollectionAssignId($value)
 */
class CollectionFinish extends \Common\Models\Collection\CollectionFinish
{

    const SCENARIO_LIST = 'list';
    const SCENARIO_CREATE = 'create';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id',
                'collection_id',
                'order_id',
                'admin_id',
                'from_status',
                'to_status',
                'collection_assign_id',
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

}
