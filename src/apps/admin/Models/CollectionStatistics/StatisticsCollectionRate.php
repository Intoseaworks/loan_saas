<?php

namespace Admin\Models\CollectionStatistics;

use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;

/**
 * Admin\Models\CollectionStatistics\StatisticsCollectionRate
 *
 * @property int $id id
 * @property string $date 统计日期
 * @property int $history_overdue_count 逾期总订单数(包含今天) 历史逾期状态总订单数
 * @property int $present_overdue_count 当前逾期订单数 当前“已逾期”状态订单数累计统计
 * @property int $today_overdue_finish 当日回款成功数  今天“逾期结清”状态订单数累计统计
 * @property int $present_overdue_finish_count 当前回款成功数  当前“逾期结清”状态订单数累计统计
 * @property int $today_collection_bad_count 坏账数  今日坏账订单累计统计
 * @property int $history_collection_bad_count 历史坏账数(包含今天)  历史坏账订单累计统计
 * @property float $today_collection_rate 当日回款率  [当日回款成功数]/([当前逾期订单数]+[当日回款成功数])
 * @property float $history_collection_rate 截止当前回款率  [截止当前回款成功数]/[逾期总订单数]
 * @property int $collection_count 今天催收记录总次数累计统计
 * @property string $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate orderByCustom($column = null, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionRate query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionRate whereCollectionCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionRate whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionRate whereHistoryCollectionBadCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionRate whereHistoryCollectionRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionRate whereHistoryOverdueCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionRate wherePresentOverdueCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionRate wherePresentOverdueFinishCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionRate whereTodayCollectionBadCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionRate whereTodayCollectionRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionRate whereTodayOverdueFinish($value)
 * @mixin \Eloquent
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\CollectionStatistics\StatisticsCollectionRate whereMerchantId($value)
 */
class StatisticsCollectionRate extends \Common\Models\CollectionStatistics\StatisticsCollectionRate
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
                'today_collection_rate' => function () {
                    return bcmul($this->today_collection_rate, 100) . '%';
                },
                'history_collection_rate' => function () {
                    return bcmul($this->history_collection_rate, 100) . '%';
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
		DATE_FORMAT(date,'{$dateFormat}') as date
		,sum(all_collection) as history_overdue_count
		,sum(collectioning) as present_overdue_count
		,sum(today_collection_success) as today_overdue_finish
		,sum(collection_success) as present_overdue_finish_count
		,sum(collection_bad) as today_collection_bad_count
		,sum(collection_record) as collection_count
from (

	select
		date_c_list.*
		,if(cr.record_count, cr.record_count, 0) as collection_record -- 催收记录次数
	from
	(
		select 
			d.date
			,c.id as collection_id
			,1 as all_collection -- 逾期总订单数
			,(CASE WHEN (bad_time is null and finish_time is null) or (bad_time is not null and d.date <= date(bad_time)) 
			or (finish_time is not null and d.date < date(finish_time)) THEN 1 ELSE 0 END) as collectioning -- 当前逾期订单数
			,(CASE WHEN (finish_time is not null and d.date = date(finish_time)) THEN 1 ELSE 0 END) as today_collection_success -- 当日回款成功数
			,(CASE WHEN (finish_time is not null and d.date >= date(finish_time)) THEN 1 ELSE 0 END) as collection_success -- 截止当前回款成功数
			,(CASE WHEN (bad_time is not null and d.date >= date(bad_time)) THEN 1 ELSE 0 END) as collection_bad -- 坏账数
		from date d 
		LEFT JOIN collection c on d.date >= date(c.created_at)
		where c.merchant_id = {$merchantId} {$dateWhere}
	) date_c_list
		LEFT JOIN (
			select date(created_at) as date, collection_id, count(1) as record_count from collection_record GROUP BY date,collection_id
	) cr on date_c_list.date = cr.date and date_c_list.collection_id = cr.collection_id

) a

GROUP BY DATE_FORMAT(date,'{$dateFormat}')
ORDER BY date desc) list";
        return DB::table(DB::raw($sql));
    }
}
