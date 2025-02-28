<?php

namespace Admin\Models\CollectionStatistics;

/**
 * Admin\Models\CollectionStatistics\StatisticsCollectionStaff
 *
 * @property int $id id
 * @property string $date 统计日期
 * @property int $staff_id 催收人员 id
 * @property string $staff_name 催收员姓名
 * @property int $allot_order 今日新增订单数   今天已分配催收单数累计统计
 * @property int $promise_paid 今日承诺还款订单数   今天“承诺还款”催收状态订单数累计统计
 * @property int $overdue_finish 今日成功订单数   今天“逾期结清”状态订单数累计统计
 * @property int $collection_bad 今日坏账订单数   今天“已坏账”状态订单数累计统计
 * @property float $collection_success_rate 今日催回率   [今日成功订单数]/[今日新增订单数]
 * @property int $collection_count 今日催收次数  今天催收记录总次数累计统计
 * @property string $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionStaff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionStaff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionStaff orderByCustom($column = null, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionStaff query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionStaff whereAllotOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionStaff whereCollectionBad($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionStaff whereCollectionCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionStaff whereCollectionSuccessRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionStaff whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionStaff whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionStaff whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionStaff whereOverdueFinish($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionStaff wherePromisePaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionStaff whereStaffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionStaff whereStaffName($value)
 * @mixin \Eloquent
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionStaff whereMerchantId($value)
 */
class StatisticsCollectionStaff extends \Common\Models\CollectionStatistics\StatisticsCollectionStaff
{
    public function safes()
    {
        return [];
    }

    public function textRules()
    {
        return [
            'array' => [
            ],
            'function' => [
                'collection_success_rate' => function () {
                    return bcmul($this->collection_success_rate, 100) . '%';
                },
            ],
        ];
    }

    /**
     * @param $param
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function search($param)
    {
        $query = $this->newQuery();

        #订单创建时间
        if ($date = array_get($param, 'date')) {
            if (count($date) == 2) {
                $dateStart = current($date);
                $dateEnd = last($date);
                $query->whereBetween('date', [$dateStart, $dateEnd]);
            }
        }

        $query->orderByCustom();

        return $query->orderBy('statistics_collection_staff.id', 'desc');
    }
}
