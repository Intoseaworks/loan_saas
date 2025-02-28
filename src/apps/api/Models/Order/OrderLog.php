<?php

namespace Api\Models\Order;

/**
 * Api\Models\Order\OrderLog
 *
 * @property int $id 订单日志id
 * @property int|null $user_id 0表示系统，其他为用户的user_id
 * @property int|null $order_id 订单id
 * @property string|null $name 比如：创建create，提交submit，取消cancel，删除delete，系统拒绝system_reject
 * @property string|null $content
 * @property int|null $admin_id 管理员id
 * @property string|null $from_status 更新前状态
 * @property string|null $to_status 更新后状态
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderLog whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderLog whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderLog whereFromStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderLog whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderLog whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderLog whereToStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderLog whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Order\OrderLog orderByCustom($column = null, $direction = 'asc')
 * @property int|null $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\Order\OrderLog whereMerchantId($value)
 */
class OrderLog extends \Common\Models\Order\OrderLog
{

    const SCENARIO_CREATE = 'create';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                //@phan-suppress-next-line PhanUndeclaredProperty
                'user_id' =>\Auth::user()->id,
            ]
        ];
    }

    public function addLog($data)
    {
        return self::model()->setScenario(self::SCENARIO_CREATE)->saveModel($data);
    }

}
