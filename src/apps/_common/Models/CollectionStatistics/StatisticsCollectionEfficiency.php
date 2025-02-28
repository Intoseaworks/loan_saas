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
class StatisticsCollectionEfficiency extends Model
{
    use StaticModel;

    /**
     * @var bool
     */
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'statistics_collection_efficiency';

    protected $fillable = [];
    protected $guarded = [];
    /**
     * @var array
     */
    protected $hidden = [];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
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
        $levelWhere = "";
        if ($level = array_get($params, 'level')) {
            $level = array_map(function ($v) {
                return "'{$v}'";
            }, (array)$level);
            $levelIn = implode(',', $level);
            $levelWhere = " AND level IN ({$levelIn}) ";
        }

        $adminIdsWhere = "";
        if ($adminIds = array_get($params, 'admin_ids')) {
            $adminIdsIn = implode(',', (array)$adminIds);
            $adminIdsWhere = " AND admin_id IN ({$adminIdsIn}) ";
        }

        $dateWhere = "";
        if ($date = array_get($params, 'date')) {
            if (count($date) == 2) {
                $dateStart = current($date);
                $dateEnd = last($date);
                $dateWhere = " AND created_at BETWEEN '{$dateStart}' and '{$dateEnd}' ";
            }
        }else{
            $dateWhere = " AND created_at BETWEEN '" . date("Y-m-d", time()-86400*7) . "' and '" . date("Y-m-d") . "' ";
        }
        $sql = "(SELECT
    DATE_FORMAT(ca.created_at,'{$dateFormat}') AS assign_data,
	s.username,
        ca.admin_id,
	ca.`level`,
	count(DISTINCT ca.order_id) AS assign_cnt,
	count(DISTINCT ca.order_id, IF( cr.id, true, null )) AS collection_cnt,
	count(DISTINCT ca.order_id, IF( cf.id AND bad_time is null, true, null )) AS finish_cnt,
	count(DISTINCT ca.order_id, IF( cf.id AND bad_time is null AND date(cf.created_at)=date_sub(curdate(),interval 1 day), true, null )) AS finish_cnt_yesterday,
	count(DISTINCT ca.order_id, IF( progress IN ( 'intentional_help', 'committed_repayment' ), true, null )) AS promise_repay_cnt,
	count(IF( cr.id, true, null )) AS cumulative_collection_cnt,
	count(DISTINCT ca.order_id, IF( ca_next.id, true, null )) AS transfer_cnt,
        0 AS finish_amount
FROM
 (select * from collection_assign WHERE id in (select max(id) from collection_assign WHERE merchant_id={$merchantId} {$dateWhere} {$adminIdsWhere} {$levelWhere} GROUP BY `level`,order_id)) ca
 LEFT JOIN collection_assign ca_next ON ca_next.id = ( SELECT min( id ) FROM collection_assign ca_id WHERE ca.collection_id = ca_id.collection_id AND ca.id < ca_id.id )
 LEFT JOIN `staff` s ON ca.admin_id = s.id
 LEFT JOIN `collection_record` cr ON ca.collection_id = cr.collection_id
 AND ca.admin_id = cr.admin_id
 LEFT JOIN `collection_finish` cf ON cf.collection_assign_id=ca.id
 LEFT JOIN `collection` c ON c.id = ca.collection_id
GROUP BY
 DATE_FORMAT(ca.created_at,'{$dateFormat}'),
 ca.admin_id,
 ca.`level`
ORDER BY
 assign_data DESC, ca.`level`, MID(username, 1, 2) ASC, MID(username,3,4) ASC) list";
        return DB::table(DB::raw($sql));
    }
}
