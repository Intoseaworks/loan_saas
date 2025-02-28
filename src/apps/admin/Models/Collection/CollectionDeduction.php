<?php

namespace Admin\Models\Collection;

use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;

/**
 * Admin\Models\Collection\CollectionDeduction
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $order_id 用户id
 * @property int $collection_id 用户id
 * @property int $from_admin_id 当前案件归属人
 * @property int $deduction_admin_id 操作人
 * @property float $overdue_fee 减免时逾期息费
 * @property float $deduction 减免金额
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDeduction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDeduction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDeduction query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDeduction whereCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDeduction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDeduction whereDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDeduction whereDeductionAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDeduction whereFromAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDeduction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDeduction whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDeduction whereOverdueFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDeduction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDeduction whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionDeduction orderByCustom($column = null, $direction = 'asc')
 * @property int $merchant_id merchant_id
 * @property int $repayment_plan_id 还款计划表id
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDeduction whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Collection\CollectionDeduction whereRepaymentPlanId($value)
 * @property int $collection_assign_id 催收分单ID
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionDeduction whereCollectionAssignId($value)
 */
class CollectionDeduction extends \Common\Models\Collection\CollectionDeduction
{

    const SCENARIO_LIST = 'list';
    const SCENARIO_CREATE = 'create';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'user_id',
                'order_id',
                'repayment_plan_id',
                'collection_id',
                'from_admin_id',
                'deduction_admin_id' => LoginHelper::getAdminId(),
                'overdue_fee',
                'deduction',
                'collection_assign_id',
            ],
        ];
    }

    public function getOne($id)
    {
        return self::where('id', '=', $id)->first();
    }

    public function create($data)
    {
        return self::model(self::SCENARIO_CREATE)->saveModel($data);
    }

}
