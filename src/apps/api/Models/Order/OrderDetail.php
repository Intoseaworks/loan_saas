<?php

namespace Api\Models\Order;

use Common\Utils\MerchantHelper;

/**
 * Api\Models\Order\OrderDetail
 *
 * @property int $order_id
 * @property string|null $key 键
 * @property string|null $value 值
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderDetail whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderDetail whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderDetail whereValue($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderDetail orderByCustom($column = null, $direction = 'asc')
 * @property int $id
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderDetail whereMerchantId($value)
 */
class OrderDetail extends \Common\Models\Order\OrderDetail
{

    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'order_id',
                'key',
                'value',
            ],
            self::SCENARIO_UPDATE => [
                'value',
            ],
        ];
    }

    /**
     * @param $data
     * @return OrderDetail|bool
     */
    public function create($data)
    {
        return $this->setScenario(self::SCENARIO_CREATE)->saveModel($data);
    }

    /**
     * @param $orderId
     * @param $orderDetailKey
     * @return mixed
     */
    /*public function getOne($orderId, $orderDetailKey)
    {
        $where = [
            'order_id' => $orderId,
            'key' => $orderDetailKey,
        ];
        return $this->where($where)->first();
    }*/

    /**
     * @param OrderDetail $orderDetail
     * @param $val
     * @return OrderDetail|bool
     */
    public function updateVal(OrderDetail $orderDetail, $val)
    {
        $data = [
            'value' => $val,
        ];
        return $orderDetail->setScenario(self::SCENARIO_UPDATE)->saveModel($data);
    }

}
