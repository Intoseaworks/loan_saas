<?php

namespace Common\Console\Models\Collection;

/**
 * Common\Console\Models\Collection\CollectionAssign
 *
 * @property int $id 完结ID
 * @property int $collection_id 催收记录ID
 * @property int $order_id 催收记录ID
 * @property int $admin_id 案件催收员ID
 * @property int $from_admin_id 来源催收员ID
 * @property string $from_status 分配前案子状态
 * @property string $to_status 分配后案子状态
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $setting_id 分单配置id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionAssign orderByCustom($column = null, $direction = 'asc')
 * @mixin \Eloquent
 */
class CollectionAssign extends \Common\Models\Collection\CollectionAssign
{

    const SCENARIO_LIST = 'list';
    const SCENARIO_CREATE_COLLECTION = 'create_collection';
    const SCENARIO_ASSIGN_AGAIN = 'assign_again';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE_COLLECTION => [
                'merchant_id',
                'order_id',
                'collection_id',
                'admin_id',
                'from_admin_id' => 0,
                'from_status' => '',
                'to_status' => Collection::STATUS_WAIT_COLLECTION,
                'setting_id',
                'level',
                'from_level',
                'overdue_days',
                'remark',
            ],
            self::SCENARIO_ASSIGN_AGAIN => [
                'merchant_id',
                'order_id',
                'collection_id',
                'admin_id',
                'from_admin_id',
                'from_status',
                'to_status' => Collection::STATUS_WAIT_COLLECTION,
                'setting_id',
                'parent_id',
                'level',
                'from_level',
                'overdue_days',
                'remark',
            ],
        ];
    }

    public function createCollection($data)
    {
        return self::model(self::SCENARIO_CREATE_COLLECTION)->saveModel($data);
    }

    public function assignAgain($data)
    {
        return self::model(self::SCENARIO_ASSIGN_AGAIN)->saveModel($data);
    }

}
