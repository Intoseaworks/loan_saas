<?php

namespace Admin\Models\CollectionStatistics;

use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;

/**
 * Admin\Models\CollectionStatistics\StatisticsCollection
 *
 * @property int $id id
 * @property string $date 统计日期
 * @property int $overdue_count 累计订单数  历史进入过逾期状态总订单数（逾期订单累计统计）
 * @property int $overdue_finish_count 累计成功数  历史进入过“逾期结清”状态订单数累计统计
 * @property int $collection_bad_count 累计坏账数  历史进入过“已坏账”状态订单数累计统计
 * @property float $collection_success_count_rate 累计回款率  [累计成功数]/[累计订单数]
 * @property int $today_overdue 今日新增订单数  今天“已逾期”状态订单数累计统计
 * @property int $today_promise_paid 今日承诺还款订单数  今天“承诺还款”催收状态订单数累计统计
 * @property int $today_overdue_finish 今日成功订单数  今天进入“逾期结清”状态订单数累计统计
 * @property int $today_collection_bad 今日坏账订单数  今天进入“已坏账”状态订单数累计统计
 * @property int $real_overdue_finish 截止当前回款成功数  当前处于“逾期结清”状态订单数累计统计
 * @property float $collection_success_rate 今日催回率  [今日成功订单数]/[今日新增订单数]
 * @property string $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection orderByCustom($column = null, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollection query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollection whereCollectionBadCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollection whereCollectionSuccessCountRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollection whereCollectionSuccessRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollection whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollection whereOverdueCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollection whereOverdueFinishCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollection whereRealOverdueFinish($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollection whereTodayCollectionBad($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollection whereTodayOverdue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollection whereTodayOverdueFinish($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollection whereTodayPromisePaid($value)
 * @mixin \Eloquent
 * @property-read \Common\Models\Order\Order $order
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollection whereMerchantId($value)
 */
class StatisticsCollection extends \Common\Models\CollectionStatistics\StatisticsCollection
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
                'collection_success_count_rate' => function () {
                    return bcmul($this->collection_success_count_rate, 100) . '%';
                },
                'collection_success_rate' => function () {
                    return bcmul($this->collection_success_rate, 100) . '%';
                },
            ],
        ];
    }

    public function search($params)
    {
        $merchantId = MerchantHelper::getMerchantId();
        $dateFormat = "%Y-%m-%d";
        if(isset($params['date_type'])){
            switch ($params['date_type']){
                case "Y":
                    $dateFormat = "%Y";
                    break;
                case "M":
                    $dateFormat = "%Y/%m";
                    break;
                case "U":
                    $dateFormat = "%Y#%U";
                    break;
            }
        }
        $dateWhere = "";
        if ($date = array_get($params, 'date')) {
            if (count($date) == 2) {
                $dateStart = current($date);
                $dateEnd = last($date);
                $dateWhere = " AND date BETWEEN '{$dateStart}' and '{$dateEnd}' ";
            }
        }else{
            $dateWhere = " AND date BETWEEN '" . date("Y-m-d", time()-86400*7) . "' and '" . date("Y-m-d") . "' ";
        }
        $sql = "(select 
	date_c_list.*
	,if(cr.promise_count, cr.promise_count, 0) as today_promise_paid
	from
(select 
		DATE_FORMAT(date,'{$dateFormat}') as date
		,sum(all_collection) as overdue_count
		,sum(collection_success) as overdue_finish_count
		,sum(collection_bad) as collection_bad_count
		,sum(today_collection_success) as today_overdue_finish
		,sum(today_collection_bad) as today_collection_bad
		,sum(today_collection_create) as today_overdue
from (

	
	select 
		d.date
		,c.id as collection_id
		,1 as all_collection -- 累计订单数
		,(CASE WHEN (c.finish_time is not null and d.date >= date(c.finish_time)) THEN 1 ELSE 0 END) as collection_success -- 累计成功数
		,(CASE WHEN (c.bad_time is not null and d.date < date(c.bad_time)) THEN 1 ELSE 0 END) as collection_bad -- 累计坏账数
		
		,(CASE WHEN d.date = date(c.created_at) THEN 1 ELSE 0 END) as today_collection_create -- 今日新增订单数
		
		,(CASE WHEN (finish_time is not null and d.date = date(finish_time)) THEN 1 ELSE 0 END) as today_collection_success -- 今日成功订单数
		,(CASE WHEN (bad_time is not null and d.date = date(bad_time)) THEN 1 ELSE 0 END) as today_collection_bad -- 今日坏账订单数
	from date d 
	LEFT JOIN collection c on d.date >= date(c.created_at)
	where c.merchant_id = {$merchantId} {$dateWhere}
) a

GROUP BY DATE_FORMAT(date,'{$dateFormat}')
) date_c_list

LEFT JOIN 

(select DATE_FORMAT(created_at,'{$dateFormat}') as date, count(DISTINCT collection_id) as promise_count from collection_record 
where progress in ('committed_repayment', 'intentional_help') and merchant_id = {$merchantId}
GROUP BY date) cr on date_c_list.date = cr.date ORDER BY date desc) list";
        return DB::table(DB::raw($sql));
    }
}
