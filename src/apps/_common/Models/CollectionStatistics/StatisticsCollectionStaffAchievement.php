<?php

namespace Common\Models\CollectionStatistics;

use Common\Traits\Model\StaticModel;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Common\Models\CollectionStatistics\StatisticsCollectionStaff
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
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionStaff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionStaff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionStaff orderByCustom($column = null, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionStaff query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionStaff whereAllotOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionStaff whereCollectionBad($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionStaff whereCollectionCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionStaff whereCollectionSuccessRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionStaff whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionStaff whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionStaff whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionStaff whereOverdueFinish($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionStaff wherePromisePaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionStaff whereStaffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionStaff whereStaffName($value)
 * @mixin \Eloquent
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionStaff whereMerchantId($value)
 */
class StatisticsCollectionStaffAchievement extends Model {

    use StaticModel;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'statistics_collection_staff_achievement';
    protected $fillable = [];
    protected $guarded = [];

    /**
     * @var array
     */
    protected $hidden = [];

    protected static function boot() {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function search($params) {
        $merchantId = MerchantHelper::getMerchantId();
        $dateFormat = "%Y-%m-%d";
        if (isset($params['date_type'])) {
            switch ($params['date_type']) {
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
        $levelWhere = "";
        if ($level = array_get($params, 'level')) {
            $level = array_map(function ($v) {
                return "'{$v}'";
            }, (array) $level);
            $levelIn = implode(',', $level);
            $levelWhere = " AND level IN ({$levelIn}) ";
        }

        $adminIdsWhere = "";
        if ($adminIds = array_get($params, 'admin_ids')) {
            $adminIdsIn = implode(',', (array) $adminIds);
            $adminIdsWhere = " AND admin_id IN ({$adminIdsIn}) ";
        }

        $dateWhere = "";
        if ($date = array_get($params, 'date')) {
            if (count($date) == 2) {
                $dateStart = current($date);
                $dateEnd = last($date);
                $dateWhere = " AND date BETWEEN '{$dateStart}' and '{$dateEnd}' ";
            }
        } else {
            $dateWhere = " AND date BETWEEN '" . date("Y-m-d", time() - 86400 * 3) . "' and '" . date("Y-m-d") . "' ";
        }

        return DB::connection("mysql_readonly")->table(DB::raw("(select 
		DATE_FORMAT(date,'{$dateFormat}') as date
		,`level`
		,admin_id
		,staff.username
		,sum(need_collection) as need_collection_count
		,sum(today_assign) as today_assign_count
		,sum(wait_collection) as wait_collection_count
		,sum(collectioning) as collectioning_count
		,sum(today_again_assign) as today_again_assign_count
		,sum(today_assign_finish) as today_assign_finish_count
		,sum(finish) as finish_count 
		,sum(bad) as bad_count
		,sum(today_assign_finish)/sum(today_assign) as today_assign_finish_rate
		,sum(finish)/sum(need_collection) as finish_rate
		,sum(bad)/sum(need_collection) as bad_rate
		,sum(principal_total) as principal_total
		,sum(repay_finish_amount) as finish_amount -- 催收成功
		,sum(repay_all_amount) as repay_all_amount -- 已还全额
		,sum(repay_part_amount) as repay_part_amount -- 部分还款
		,concat(LEAST(100,round(sum(repay_finish_amount) / sum(principal_total)* 100,2)), '%')  as repay_finish_rate -- 成功金额还款率
		,concat(LEAST(100,round(sum(repay_all_amount) / sum(principal_total)* 100,2)), '%') as repay_rate -- 总体金额还款率
from (

		select 
		date
		,ca_in.`level`
		,ca_in.admin_id
		,(CASE WHEN (bad_time is null or d.date < bad_time) THEN 1 ELSE 0 END) as need_collection -- 总需处理
		,(CASE WHEN (bad_time is null or d.date < bad_time) and ca_in.start_date BETWEEN d.date and DATE_FORMAT(DATE_ADD(d.date,INTERVAL 1 DAY),'%Y-%m-%d') THEN 1 ELSE 0 END) as today_assign -- 当日新分配
		,(CASE WHEN (bad_time is null or d.date < bad_time) and collection_time is null and finish_time is null THEN 1 ELSE 0 END) as wait_collection -- 待处理
		,(CASE WHEN (bad_time is null or d.date < bad_time) 
		and collection_time 
		and (finish_time is null or d.date < finish_time) 
		and (next_created_at is null or DATE_FORMAT(DATE_ADD(d.date,INTERVAL 1 DAY),'%Y-%m-%d') < next_created_at)
		THEN 1 ELSE 0 END) as collectioning -- 处理中
		,(CASE WHEN (bad_time is null or d.date < bad_time) and next_created_at and d.date = date(next_created_at) THEN 1 ELSE 0 END) as today_again_assign -- 当日分配走
		,(CASE WHEN (bad_time is null or d.date < bad_time) and finish_time and d.date = date(start_date) and date(start_date) = date(finish_time) and next_created_at is null THEN 1 ELSE 0 END) as today_assign_finish -- 当日毕成功
		,(CASE WHEN (bad_time is null or d.date < bad_time) and finish_time and d.date = date(finish_time) and next_created_at is null THEN 1 ELSE 0 END) as finish -- 总成功
		,(CASE WHEN bad_time and d.date = date(bad_time) THEN 1 ELSE 0 END) as bad -- 当日坏账
		,principal_total -- 应还总金额
		,date(rd.actual_paid_time)
		,rd.order_id
		,sum(case WHEN d.date=date(rd.actual_paid_time) then paid_amount else 0 end) as repay_all_amount -- 已还全额
		,sum(case WHEN d.date=date(rd.actual_paid_time) AND rp_status=2 then paid_amount else 0 end) as repay_finish_amount -- 结清还款
                ,sum(case WHEN d.date=date(rd.actual_paid_time) AND rp_status=4 then paid_amount else 0 end) as repay_part_amount -- 未结清还款
	from date d LEFT JOIN 
	(
		-- 处理期间
		select
		ca_in_child.*
		,cr.created_at as collection_time
	 ,rp.principal_total as principal_total
	 ,rp.`status` as rp_status
		from (
			SELECT
			 ca.created_at AS start_date
       ,ca.id
			 ,ca.collection_id
			 ,ca.order_id
			 ,(CASE  
			 WHEN ca_next.created_at THEN ca_next.created_at
			 WHEN c.finish_time THEN c.finish_time
			 WHEN c.bad_time THEN c.bad_time
			 ELSE now() END) AS end_date
			 ,ca.admin_id
			 ,ca.`level`
			 ,ca_next.created_at as next_created_at
			 ,c.finish_time
			 ,c.bad_time
			FROM collection_assign ca
			LEFT JOIN collection c on ca.collection_id = c.id
			LEFT JOIN collection_assign ca_next on 
			ca_next.id = ( SELECT min(id) FROM collection_assign ca_id WHERE ca.collection_id = ca_id.collection_id and ca.id < ca_id.id)
			WHERE ca.merchant_id ={$merchantId} and c.id is not null -- and (ca.overdue_days is null or ca.overdue_days > 0)
		) ca_in_child
		LEFT JOIN collection_record cr on 
		cr.id = ( SELECT min(id) FROM collection_record cr_id 
		WHERE ca_in_child.collection_id = cr_id.collection_id 
		and ca_in_child.admin_id=cr_id.admin_id
		and created_at BETWEEN start_date and end_date)
		INNER JOIN repayment_plan rp ON ca_in_child.order_id=rp.order_id AND rp.installment_num=1
	) ca_in on d.date BETWEEN start_date and end_date or d.date = date(start_date)
		LEFT JOIN repay_detail rd ON rd.order_id=ca_in.order_id AND rd.`status`=1 AND rd.repay_type not in(6,7) AND rd.actual_paid_time BETWEEN start_date and end_date
		 where `level` is not null and ca_in.admin_id is not null
		and (ca_in.next_created_at is null or date(ca_in.next_created_at) > date)
		group by date,collection_id

) date_ca 

left join staff on staff.id = date_ca.admin_id
where `level` is not null and admin_id is not null {$dateWhere} {$levelWhere} {$adminIdsWhere}
GROUP BY DATE_FORMAT(date,'{$dateFormat}'),`level`,admin_id ORDER BY date desc,finish_amount desc, username asc, `level` asc) list"));
    }

    public function searchNew($params) {
        $merchantId = MerchantHelper::getMerchantId();
        $dateFormat = "%Y-%m-%d";
        $dateWhere = "";
        if (isset($params['date_type'])) {
            switch ($params['date_type']) {
                case "Y":
                    $dateFormat = "%Y";
                    break;
                case "M":
                    $dateFormat = "%Y/%m";
                    break;
                case "U":
                    $dateFormat = "%Y#%U";
                    break;
                case "D":
                    $dateWhere = " AND date BETWEEN '" . date("Y-m-d", time() - 86400 * 7) . "' and '" . date("Y-m-d") . "' ";
                    break;
            }
        }
        $levelWhere = "";
        if ($level = array_get($params, 'level')) {
            $level = array_map(function ($v) {
                return "'{$v}'";
            }, (array) $level);
            $levelIn = implode(',', $level);
            $levelWhere = " AND level IN ({$levelIn}) ";
        }

        $adminIdsWhere = "";
        if ($adminIds = array_get($params, 'admin_ids')) {
            $adminIdsIn = implode(',', (array) $adminIds);
            $adminIdsWhere = " AND admin_id IN ({$adminIdsIn}) ";
        }

        if ($date = array_get($params, 'date')) {
            if (count($date) == 2) {
                $dateStart = current($date);
                $dateEnd = last($date);
                $dateWhere = " AND date BETWEEN '{$dateStart}' and '{$dateEnd}' ";
            }
        }

        $sql = "SELECT
                DATE_FORMAT(date,'{$dateFormat}') as date
		,`level`
		,admin_id
		,username
		,sum(need_collection_count) as need_collection_count
		,sum(today_assign_count) as today_assign_count
		,sum(wait_collection_count) as wait_collection_count
		,sum(collectioning_count) as collectioning_count
		,sum(today_again_assign_count) as today_again_assign_count
		,sum(today_assign_finish_count) as today_assign_finish_count
		,sum(finish_count) as finish_count 
		,sum(bad_count) as bad_count
		,sum(today_assign_finish_count)/sum(today_assign_count) as today_assign_finish_rate
		,sum(finish_count)/sum(need_collection_count) as finish_rate
		,sum(bad_count)/sum(need_collection_count) as bad_rate
		,sum(principal_total) as principal_total
		,sum(finish_amount) as finish_amount -- 催收成功
		,sum(repay_all_amount) as repay_all_amount -- 已还全额
		,sum(repay_part_amount) as repay_part_amount -- 部分还款
		,concat(LEAST(100,round(sum(finish_amount) / sum(principal_total)* 100,2)), '%')  as repay_finish_rate -- 成功金额还款率
		,concat(LEAST(100,round(sum(repay_all_amount) / sum(principal_total)* 100,2)), '%') as repay_rate -- 总体金额还款率 
                FROM statistics_collection_staff_peso WHERE merchant_id={$merchantId} {$dateWhere} {$levelWhere} {$adminIdsWhere} GROUP BY date,level,admin_id,username "
                . " ORDER BY date desc,finish_amount desc, username asc, `level` asc";
            return DB::table(DB::raw("({$sql}) list"));
    }

}
