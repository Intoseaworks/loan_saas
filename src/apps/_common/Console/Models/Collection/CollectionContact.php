<?php

namespace Common\Console\Models\Collection;

/**
 * Console\Models\Collection\CollectionContact
 *
 * @property int $id
 * @property int $order_id 订单ID
 * @property int $user_id 用户ID
 * @property int $collection_id 订单ID
 * @property string $type 联系来源类型
 * @property string $fullname 姓名
 * @property string $contact 联系值（手机号）
 * @property string $relation 关系
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property string $content
 * @mixin \Eloquent
 */
class CollectionContact extends \Common\Models\Collection\CollectionContact
{

    const SCENARIO_LIST = 'list';
    const SCENARIO_CREATE = 'create';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id',
                'order_id',
                'user_id',
                'collection_id',
                'type',
                'fullname',
                'contact',
                'relation',
            ],
        ];
    }

    public function create($data)
    {
        return self::model(self::SCENARIO_CREATE)->saveModel($data);
    }

}
