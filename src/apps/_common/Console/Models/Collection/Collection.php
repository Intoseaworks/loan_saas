<?php

namespace Common\Console\Models\Collection;

use Common\Utils\Data\DateHelper;

/**
 * Common\Console\Models\Collection\Collection
 *
 * @property int $id
 * @property int $admin_id 催收人员id
 * @property int $user_id 用户id
 * @property int $order_id 订单id
 * @property string $status 催收状态
 * @property string $level 催收等级
 * @property string|null $assign_time 分配时间
 * @property string|null $finish_time 完结时间
 * @property string|null $bad_time 坏账时间
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @mixin \Eloquent
 */
class Collection extends \Common\Models\Collection\Collection
{

    const SCENARIO_LIST = 'list';
    const SCENARIO_CREATE = 'create';
    const SCENARIO_ASSIGN_AGAIN = 'assign_again';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id',
                'admin_id',
                'user_id',
                'order_id',
                'status',
                'level',
                'assign_time' => DateHelper::dateTime(),
            ],
            self::SCENARIO_ASSIGN_AGAIN => [
                'admin_id',
                'user_id',
                'order_id',
                'status' => self::STATUS_WAIT_COLLECTION,
                'level',
                'assign_time' => DateHelper::dateTime(),
            ],
        ];
    }

    public function collectionDetail($class = CollectionDetail::class)
    {
        return parent::collectionDetail($class);
    }

    public function create($data)
    {
        return self::model(self::SCENARIO_CREATE)->saveModel($data);
    }

    public function assignAgain(Collection $collection, $data)
    {
        return $collection->setScenario(self::SCENARIO_ASSIGN_AGAIN)->saveModel($data);
    }

    public function needUpdateCollections($overdueDaysStart, $overdueDaysEnd, $level)
    {
        //\DB::enableQueryLog();
        $query = $this->newQuery();
        $query->whereIn('status', array_merge(Collection::STATUS_NOT_COMPLETE, Collection::STATUS_HIDDEN))
            ->where('level', '!=', $level)
            ->whereHas('lastRepaymentPlan', function ($query) use ($overdueDaysStart, $overdueDaysEnd) {
                //$query->whereBetween('repayment_plan.overdue_days', [$overdueDaysStart, $overdueDaysEnd]);
                # 已应还时间为准，最大时间向上取date
                //$query->whereBetween('repayment_plan.appointment_paid_time', [DateHelper::subDays($overdueDaysEnd), DateHelper::subDays($overdueDaysStart - 1)]);
                $query->whereBetween('repayment_plan.appointment_paid_time', [DateHelper::subDays($overdueDaysEnd), DateHelper::subDays($overdueDaysStart)]);
            });
        return $query->get();
        //$query->get();dd(\DB::getQueryLog());
    }

}
