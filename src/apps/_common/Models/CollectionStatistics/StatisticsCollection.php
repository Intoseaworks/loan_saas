<?php

namespace Common\Models\CollectionStatistics;

use Common\Models\Order\Order;
use Common\Traits\Model\StaticModel;
use Common\Models\Collection\CollectionAssign;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Common\Services\Order\OrderServer;
use Common\Utils\Data\DateHelper;
use Common\Utils\Data\MoneyHelper;
use Illuminate\Support\Facades\Cache;
/**
 * Common\Models\CollectionStatistics\StatisticsCollection
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
 * @property-read \Common\Models\Order\Order $order
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection orderByCustom($column = null, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection whereCollectionBadCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection whereCollectionSuccessCountRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection whereCollectionSuccessRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection whereOverdueCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection whereOverdueFinishCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection whereRealOverdueFinish($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection whereTodayCollectionBad($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection whereTodayOverdue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection whereTodayOverdueFinish($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection whereTodayPromisePaid($value)
 * @mixin \Eloquent
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\CollectionStatistics\StatisticsCollection whereMerchantId($value)
 */
class StatisticsCollection extends Model
{
    use StaticModel;

    /**
     * @var bool
     */
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'statistics_collection';

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

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'id');
    }
    
    /**
     * 催收总金额
     * @param type $date
     * @param type $level
     * @param type $dateFormat
     * @param type $adminId
     * @return type
     */
    public function collectTotalAmount($date, $level, $dateFormat, $adminId = 0) {
        $cacheKey = md5($date.$level.$dateFormat.$adminId);
        return Cache::remember($cacheKey, 2 * 60, function () use ($date, $level, $dateFormat, $adminId) {
                    $totalAmount = 0;

                    $list = CollectionAssign::model()->newQuery()->where(DB::raw("DATE_FORMAT(created_at,'{$dateFormat}')"), $date);
                    $list->where('level', $level);
                    if ($adminId) {
                        $list->where("admin_id", $adminId);
                    }
                    foreach ($list->get() as $item) {
                        $order = Order::model()->getOne($item->order_id);
                        $totalAmount += $this->getOrderRepayAmountByDate($order, substr($item->create, 0, 10));
                    }
                    return round($totalAmount,2);
                });
    }

    public function getOrderRepayAmountByDate(Order $order, $date) {
        $overdueDays = OrderServer::server()->getOverdueDays($order->loan_days, $order->paid_time, $date);
        $loanDays = $overdueDays >= 0 ? $order->loan_days : $order->loan_days - (abs($overdueDays));
        $repayAmount = 0;
        //计算息费
        $order->getPaidPrincipal();
        $repayAmount += $order->getPaidPrincipal() + OrderServer::server()->getInterestFee($order->getPaidPrincipal(), $loanDays, $order->daily_rate);
        //计算逾期费用
        if ($overdueDays > 0) {
            list($overdueFee, $managementFee) = $order->getManagementAndOverdueFee($overdueDays);
            $repayAmount += $overdueFee;
            $repayAmount += $managementFee;

            //计算过去订单逾期费率
            if ($overdueFee == 0) {
                $repayAmount += MoneyHelper::round2point($order->getPaidPrincipal() * $order->overdue_rate * $overdueDays);
            }
        }
        return $repayAmount;
    }

}
