<?php

namespace Common\Models\CollectionStatistics;

use Common\Redis\CollectionStatistics\CollectionStatisticsRedis;
use Common\Traits\Model\StaticModel;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

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
class StatisticsCollectionStaff extends Model
{
    use StaticModel;

    /**
     * @var bool
     */
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'statistics_collection_staff';

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

    /**
     * 数据表字段 对应 redis field 关系
     */
    const RELATED_REDIS_FIELD = [
        'allot_order' => CollectionStatisticsRedis::FIELD_STAFF_ALLOT_ORDER,
        'promise_paid' => CollectionStatisticsRedis::FIELD_STAFF_PROMISE_PAID,
        'overdue_finish' => CollectionStatisticsRedis::FIELD_STAFF_OVERDUE_FINISH,
        'collection_bad' => CollectionStatisticsRedis::FIELD_STAFF_COLLECTION_BAD,
        'collection_count' => CollectionStatisticsRedis::FIELD_STAFF_COLLECTION_COUNT,
    ];

    public function sortCustom()
    {
        return [
            'date' => [
                'field' => 'date',
            ],
        ];
    }

    public static function add($date, $data)
    {
        self::clearByDateAndAdminId($date, array_column($data, 'staff_id'));

        $insert = [];
        $insertData = [];
        foreach ($data as $item) {
            $insertData['merchant_id'] = MerchantHelper::getMerchantId();
            $insertData['date'] = $date;
            $insertData['staff_id'] = $item['staff_id'];
            $insertData['staff_name'] = $item['staff_name'] ?? '';
            $insertData['allot_order'] = $item['allot_order'] ?? 0;
            $insertData['promise_paid'] = $item['promise_paid'] ?? 0;
            $insertData['overdue_finish'] = $item['overdue_finish'] ?? 0;
            $insertData['collection_bad'] = $item['collection_bad'] ?? 0;
            $insertData['collection_success_rate'] = $item['collection_success_rate'] ?? 0;
            $insertData['collection_count'] = $item['collection_count'] ?? 0;

            $insert[] = $insertData;
        }

        return self::insert($insert);
    }

    public static function clearByDateAndAdminId($date, $adminIds)
    {
        return self::where('date', $date)
            ->whereIn('staff_id', (array)$adminIds)
            ->delete();
    }
}
