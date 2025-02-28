<?php

namespace Admin\Models\Collection;

use Admin\Models\Order\Order;
use Admin\Models\Staff\Staff;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;

/**
 * Admin\Models\Collection\CollectionRecord
 *
 * @property int $id
 * @property int $collection_id 催收记录ID
 * @property int $order_id 管理员ID
 * @property int $admin_id 管理员ID
 * @property string $fullname 姓名
 * @property string|null $relation 亲戚关系
 * @property string|null $contact 联系值（手机号）
 * @property string|null $promise_paid_time 承诺还款时间
 * @property string $remark 备注
 * @property string $dial 联系结果 （正常联系，无法联系...）
 * @property string $progress 催收进度 （承诺还款，无意向...）
 * @property string $from_status 催记前案子状态
 * @property string $to_status 催记后案子状态
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereDial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereFromStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord wherePromisePaidTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereToStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $overdue_days 当前逾期天数
 * @property float $reduction_fee 当前减免金额
 * @property string $level 当前催收等级
 * @property float $receivable_amount 当前应还金额
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereOverdueDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereReceivableAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereReductionFee($value)
 * @property int $user_id 管理员ID
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionRecord orderByCustom($column = null, $direction = 'asc')
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionRecord whereMerchantId($value)
 * @property int $collection_assign_id 催收分单ID
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRecord whereCollectionAssignId($value)
 */
class CollectionRecord extends \Common\Models\Collection\CollectionRecord
{

    const SCENARIO_LIST = 'list';
    const SCENARIO_CREATE = 'create';

    public function safes()
    {
        return [
            self::SCENARIO_LIST => [
                'id',
                'no',
                'fullname',
                'relation',
                'contact',
                'dial',
                'progress',
                'overdue_days',
                'remark',
                'reduction_fee',
                'level',
                'admin_id',
                'created_at',
                'created_at',
                'receivable_amount',
                'status',
                'contact_method',
                'promise_paid_time_slot'
            ],
            self::SCENARIO_CREATE => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'fullname',
                'relation',
                'dial',
                'progress',
                'promise_paid_time',
                'promise_paid_time_slot',
                'remark',
                'collection_id',
                'order_id',
                'user_id',
                'admin_id' => LoginHelper::getAdminId(),
                'contact',
                'from_status',
                'to_status',
                'overdue_days',
                'reduction_fee',
                'level',
                'receivable_amount',
                'collection_assign_id',
                'contact_method',
            ],
        ];
    }

    public function textRules()
    {
        return [
            'array' => [
                'to_status' => ts(Collection::STATUS, 'collection'),
                'progress' => Collection::model()->getProgressAll(),
                'dial' => ts(Collection::DIAL_ALL, 'collection'),
                'relation' => ts(CollectionContact::RELATION, 'collection'),
            ],
            'function' => [
                'id' => function () {
                    $this->staff && $this->staff->setScenario(Staff::SCENARIO_INFO)->getText();
                    $this->contact_method_text = self::CONTACT_METHOD[$this->contact_method] ?? '---';
                    # 性能问题 不查order
                    // $this->order && $this->order->setScenario(Order::SCENARIO_LIST)->getText();
                }
            ]
        ];
    }

    public function search($params, $with = [], $defaultSort = null)
    {
        $query = self::query()->with($with);

        $query->orderByCustom($defaultSort);

        if ($orderId = array_get($params, 'order_id')) {
            $query->where('order_id', $orderId);
        }

        return $query;
    }

    public function staff($class = Staff::class)
    {
        return parent::staff($class);
    }

    public function order($class = Order::class)
    {
        return parent::order($class);
    }

    public function create($data)
    {
        return self::model(self::SCENARIO_CREATE)->saveModel($data);
    }

}
