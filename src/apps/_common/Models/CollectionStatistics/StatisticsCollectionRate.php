<?php

namespace Common\Models\CollectionStatistics;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\CollectionStatistics\StatisticsCollectionRate
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
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate orderByCustom($column = null, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate whereCollectionCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate whereHistoryCollectionBadCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate whereHistoryCollectionRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate whereHistoryOverdueCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate wherePresentOverdueCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate wherePresentOverdueFinishCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate whereTodayCollectionBadCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate whereTodayCollectionRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate whereTodayOverdueFinish($value)
 * @mixin \Eloquent
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollectionRate whereMerchantId($value)
 */
class StatisticsCollectionRate extends Model
{
    use StaticModel;

    /**
     * @var bool
     */
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'statistics_collection_rate';
    protected $fillable = [];
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    /**
     * @var array
     */
    protected $hidden = [];

    public function sortCustom()
    {
        return [
            'date' => [
                'field' => 'date',
            ],
        ];
    }

    /**
     * 获取 小于等于指定日期之前 的最后一条记录(防止出现中间数据中断的情况)
     * @param $date
     * @return \Illuminate\Database\Eloquent\Builder|Model|object|null
     */
    public static function getByDate($date)
    {
        return self::query()->where('date', '<=', $date)
            ->orderBy('date', 'desc')
            ->first();
    }
}
