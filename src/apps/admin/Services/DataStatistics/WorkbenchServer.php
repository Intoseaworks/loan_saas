<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\DataStatistics;

use Admin\Services\BaseService;
use Common\Models\Approve\ApprovePool;
use Common\Models\Approve\ApprovePoolLog;
use Common\Models\Collection\Collection;
use Common\Models\Collection\CollectionRecord;
use Common\Models\Order\Order;
use Common\Models\Order\OrderDetail;
use Common\Models\Order\RepaymentPlan;
use Common\Models\Statistics\StatisticsData;
use Common\Models\Trade\TradeLog;
use Common\Models\User\User;
use Common\Models\User\UserAuth;
use Common\Utils\Data\DateHelper;
use Common\Utils\Data\StatisticsHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WorkbenchServer extends BaseService
{

    public $todayTimeStart, $todayTimeNow, $todayTimeEnd,
        $yesterdayTimeStart, $yesterdayTimeNow, $yesterdayTimeEnd,
        $weekdayTimeStart, $weekdayTimeNow, $weekdayTimeEnd;

    public $todayNewCollectionCount;

    public function __construct()
    {
        $this->todayTimeStart = DateHelper::date();
        $this->todayTimeNow = DateHelper::dateTime();
        $this->todayTimeEnd = DateHelper::date() . ' 23:59:59';
        $this->yesterdayTimeStart = DateHelper::subDays(1);
        $this->yesterdayTimeNow = DateHelper::subDaysTime(1);
        $this->yesterdayTimeEnd = DateHelper::subDays(1) . ' 23:59:59';
        $this->weekdayTimeStart = DateHelper::subDays(7);
        $this->weekdayTimeNow = DateHelper::subDaysTime(7);
        $this->weekdayTimeEnd = DateHelper::subDaysTime(7) . ' 23:59:59';
    }

    public function setDate($date)
    {
        $this->todayTimeStart = $date;
        $this->todayTimeNow = $this->todayTimeStart . ' 23:59:59';
        $this->todayTimeEnd = $this->todayTimeStart . ' 23:59:59';
        $this->yesterdayTimeStart = DateHelper::subDays(1, 'Y-m-d', $date);
        $this->yesterdayTimeNow = $this->yesterdayTimeStart . ' 23:59:59';
        $this->yesterdayTimeEnd = $this->yesterdayTimeStart . ' 23:59:59';
        $this->weekdayTimeStart = DateHelper::subDays(7);
        $this->weekdayTimeNow = $this->weekdayTimeStart . ' 23:59:59';
        $this->weekdayTimeEnd = $this->weekdayTimeStart . ' 23:59:59';
    }

    /**
     * @param $param
     * @return mixed
     */
    public function getIndex($param)
    {
        if ($date = array_get($param, 'date')) {
            $this->setDate($date);
        }
        $dataHandler = function () {
            return [
                StatisticsData::TYPE_REGISTER => $this->getRegister(),
                StatisticsData::TYPE_AUTH => $this->getAuth(),
                StatisticsData::TYPE_LOAN => $this->getLoan(),
                StatisticsData::TYPE_PASS => $this->getPass(),
                StatisticsData::TYPE_REMIT => $this->getRemit(),
                StatisticsData::TYPE_REMIT_AMOUNT => $this->getRemitAmount(),
                StatisticsData::TYPE_REPAY => $this->getRepay(),
                StatisticsData::STATISTICS_OVERDUE_SUM => $this->getOverdueSum(),
                StatisticsData::STATISTICS_OVERDUE_COUNT => $this->getOverdueCount(),
                'promise_paid_count' => $this->getPromisePaidCount(),
                'collection_success_count' => $this->getCollectionSuccessCount(),
                'collection_bad_count' => $this->getCollectionBadCount(),
                'collection_record_count' => $this->getCollectionRecordCount(),
                'approve_total_count' => $this->totalCount(),
                'approve_system_pass' => $this->systemPass(),
                'approve_manual_count' => $this->manualCount(),
                'approve_wait_manual_approve' => $this->waitManualApprove(),
                'approve_manual_pass' => $this->manualPass(),
                'approve_call_count' => $this->callCount(),
                'approve_wait_call_approve' => $this->waitCallApprove(),
                'approve_call_pass' => $this->callPass(),
                'approve_wait_sign' => $this->waitSign(),
                'approve_sign_contract' => $this->signContract(),
            ];
        };
        //测试期间关闭缓存
        return $dataHandler();

        $cacheKey = 'statistics:workbench:index';
        /** 设置缓存 1分钟 */
        return Cache::remember($cacheKey, 1, function () use ($dataHandler) {
            return $dataHandler();
        });
    }

    /**
     * 注册数
     *
     * @return array
     */
    public function getRegister()
    {
        list($todayConut, $chainRatioByYesterday, $chainRatioByweekdayConut) = $this->getChainRatioByModel(new User(), 'created_at');

        $todayNewUserOrderRemitCount = Order::model()
            ->whereBetween('paid_time', [$this->todayTimeStart, $this->todayTimeNow])
            ->where('quality', Order::QUALITY_NEW)
            ->count();
        $todayRegisterRemitPercent = StatisticsHelper::percent($todayNewUserOrderRemitCount, $todayConut);

        return [
            $todayConut,
            $chainRatioByYesterday,
            $chainRatioByweekdayConut,
            t('注册放款转化率', 'statistics') . "   {$todayRegisterRemitPercent} %",
        ];
    }

    /**
     * 环比计算
     *
     * @param $model
     * @param $key
     * @return array
     */
    public function getChainRatioByModel($model, $key, $sumKey = '', $callFunc = '')
    {
        $todayConutSum = $this->getCountSumByModel($model, $key, $this->todayTimeStart, $this->todayTimeNow, $sumKey, $callFunc);
        $yesterdayConutSum = $this->getCountSumByModel($model, $key, $this->yesterdayTimeStart, $this->yesterdayTimeNow, $sumKey, $callFunc);
        $weekdayConutSum = $this->getCountSumByModel($model, $key, $this->weekdayTimeStart, $this->weekdayTimeNow, $sumKey, $callFunc);

        return [
            $todayConutSum,
            StatisticsHelper::chainRatio($todayConutSum, $yesterdayConutSum),
            StatisticsHelper::chainRatio($todayConutSum, $weekdayConutSum),
        ];
    }

    /**
     * 环比查询
     *
     * @param $model
     * @param $key
     * @param $TimeStart
     * @param $TimeEnd
     * @param string $sumKey
     * @return mixed
     */
    public function getCountSumByModel($model, $keys, $TimeStart, $TimeEnd, $sumKey = '', $callFunc = '')
    {
        $modelClone = clone $model;
        $keys = (array)$keys;
        foreach ($keys as $key) {
            $modelClone = $modelClone->whereBetween($key, [$TimeStart, $TimeEnd]);
        }
        if ($callFunc != '') {
            return $callFunc($modelClone);
        }
        if ($sumKey != '') {
            return $modelClone->sum($sumKey);
        }
        return $modelClone->count();
    }

    /**
     * 完成认证数
     *
     * @return array
     */
    public function getAuth()
    {
        list($todayConut, $chainRatioByYesterday, $chainRatioByweekdayConut) = $this->getAuthChainRatioCount();

        $todayOldUserAuthCount = $this->getCountByAuth($this->todayTimeStart, $this->todayTimeNow, User::QUALITY_OLD);
        $todayNewUserAuthCount = $this->getCountByAuth($this->todayTimeStart, $this->todayTimeNow, User::QUALITY_NEW);

        return [
            $todayConut,
            $chainRatioByYesterday,
            $chainRatioByweekdayConut,
            " {$todayNewUserAuthCount}/ {$todayOldUserAuthCount}" . t('（新/复贷）', 'statistics'),
        ];
    }

    /**
     * 当日认证环比
     *
     * @return array
     */
    public function getAuthChainRatioCount()
    {
        $todayConutSum = $this->getCountByAuth($this->todayTimeStart, $this->todayTimeNow);
        $yesterdayConutSum = $this->getCountByAuth($this->yesterdayTimeStart, $this->yesterdayTimeNow);
        $weekdayConutSum = $this->getCountByAuth($this->weekdayTimeStart, $this->weekdayTimeNow);

        return [
            $todayConutSum,
            StatisticsHelper::chainRatio($todayConutSum, $yesterdayConutSum),
            StatisticsHelper::chainRatio($todayConutSum, $weekdayConutSum),
        ];
    }

    /**
     * 当日认证环比查询
     *
     * @param $TimeStart
     * @param $TimeEnd
     * @return mixed
     */
    public function getCountByAuth($TimeStart, $TimeEnd, $quality = '')
    {
        $query = UserAuth::model()
            ->select(DB::raw('count(DISTINCT user_id) as user_count'))
            ->where('type', UserAuth::TYPE_COMPLETED)
            ->whereBetween('time', [$TimeStart, $TimeEnd]);
        if ($quality !== '') {
            $query->where('quality', $quality);
        }
        return $query->first()->user_count;
    }

    /**
     * 借款申请数
     *
     * @return array
     */
    public function getLoan()
    {
        list($todayConut, $chainRatioByYesterday, $chainRatioByweekdayConut) = $this->getChainRatioByModel(new Order(), 'created_at');

        $todayOldUserOrderCount = Order::model()
            ->whereBetween('created_at', [$this->todayTimeStart, $this->todayTimeNow])
            ->where('quality', Order::QUALITY_OLD)
            ->count();
        $todayNewUserOrderCount = Order::model()
            ->whereBetween('created_at', [$this->todayTimeStart, $this->todayTimeNow])
            ->where('quality', Order::QUALITY_NEW)
            ->count();

        return [
            $todayConut,
            $chainRatioByYesterday,
            $chainRatioByweekdayConut,
            " {$todayNewUserOrderCount}/ {$todayOldUserOrderCount}" . t('（新/复贷）', 'statistics'),
        ];
    }

    /**
     * 申请通过数
     *
     * @return array
     */
    public function getPass()
    {
        list($todayConut, $chainRatioByYesterday, $chainRatioByweekdayConut) = $this->getChainRatioByModel(new Order(), 'pass_time');

        $todayOldUserOrderPassCount = Order::model()
            ->whereBetween('pass_time', [$this->todayTimeStart, $this->todayTimeNow])
            ->where('quality', Order::QUALITY_OLD)
            ->count();
        $todayNewUserOrderPassCount = Order::model()
            ->whereBetween('pass_time', [$this->todayTimeStart, $this->todayTimeNow])
            ->where('quality', Order::QUALITY_NEW)
            ->count();

        return [
            $todayConut,
            $chainRatioByYesterday,
            $chainRatioByweekdayConut,
            " {$todayNewUserOrderPassCount}/ {$todayOldUserOrderPassCount}" . t('（新/复贷）', 'statistics'),
        ];
    }

    /**
     * 放款数
     *
     * @return array
     */
    public function getRemit()
    {
        list($todayConut, $chainRatioByYesterday, $chainRatioByweekdayConut) = $this->getChainRatioByModel(new Order(), 'paid_time');

        $todayOldUserOrderRemitCount = Order::model()
            ->whereBetween('paid_time', [$this->todayTimeStart, $this->todayTimeNow])
            ->where('quality', Order::QUALITY_OLD)
            ->count();
        $todayNewUserOrderRemitCount = Order::model()
            ->whereBetween('paid_time', [$this->todayTimeStart, $this->todayTimeNow])
            ->where('quality', Order::QUALITY_NEW)
            ->count();

        return [
            $todayConut,
            $chainRatioByYesterday,
            $chainRatioByweekdayConut,
            " {$todayNewUserOrderRemitCount}/ {$todayOldUserOrderRemitCount}" . t('（新/复贷）', 'statistics'),
        ];
    }

    /**
     * 获取放款金额
     * @return array
     */
    public function getRemitAmount()
    {
        $model = new TradeLog();
        $timeField = 'trade_result_time';
        $sumField = 'trade_amount';

        $where = [
            'business_type' => TradeLog::BUSINESS_TYPE_MANUAL_REMIT,
            'trade_result' => TradeLog::TRADE_RESULT_SUCCESS,
            'trade_evolve_status' => TradeLog::TRADE_EVOLVE_STATUS_OVER,
        ];

        list($todaySum, $chainRatioByYesterday, $chainRatioByWeekday) = $this->getChainRatioByModel($model, $timeField, $sumField, function (Builder $query) use ($sumField, $where) {
            return $query->where($where)->sum($sumField);
        });

        $todayOldUserOrderRemitSum = TradeLog::query()
            ->whereBetween($timeField, [$this->todayTimeStart, $this->todayTimeNow])
            ->where($where)
            ->whereHas('order', function ($query) {
                $query->where('quality', Order::QUALITY_OLD);
            })
            ->sum($sumField);
        $todayNewUserOrderRemitSum = TradeLog::query()
            ->whereBetween($timeField, [$this->todayTimeStart, $this->todayTimeNow])
            ->where($where)
            ->whereHas('order', function ($query) {
                $query->where('quality', Order::QUALITY_NEW);
            })
            ->sum($sumField);

        $todayNewUserOrderRemitSum = StatisticsHelper::numberFormat($todayNewUserOrderRemitSum);
        $todayOldUserOrderRemitSum = StatisticsHelper::numberFormat($todayOldUserOrderRemitSum);;

        return [
            StatisticsHelper::numberFormat($todaySum),
            $chainRatioByYesterday,
            $chainRatioByWeekday,
            " {$todayNewUserOrderRemitSum}/ {$todayOldUserOrderRemitSum}" . t('（新/复贷）', 'statistics'),
        ];
    }

    /**
     * 还款额
     *
     * @return array
     */
    public function getRepay()
    {
        //list($todaySum, $chainRatioByYesterdaySum, $chainRatioByweekdaySum) = $this->getChainRatioByModel(new RepaymentPlan(), 'repay_time', 'repay_amount');
        list($todaySum, $chainRatioByYesterdaySum, $chainRatioByweekdaySum) = $this->getChainRatioByModel(
            TradeLog::where('trade_type', TradeLog::TRADE_TYPE_RECEIPTS)
                ->where('trade_result', TradeLog::TRADE_RESULT_SUCCESS),
            'trade_result_time', 'trade_amount');

        $todayNeedRepaySum = RepaymentPlan::model()
            ->leftJoin('order', 'order.id', '=', 'repayment_plan.order_id')
            ->whereBetween('appointment_paid_time', [$this->todayTimeStart, $this->todayTimeNow])
            ->sum('order.principal');
        $todayNeedRepaySum = StatisticsHelper::numberFormat($todayNeedRepaySum);
        return [
            StatisticsHelper::numberFormat($todaySum),
            $chainRatioByYesterdaySum,
            $chainRatioByweekdaySum,
            t('日应还金额    ¥', 'statistics') . "{$todayNeedRepaySum}",
        ];
    }

    /**
     * 逾期额
     *
     * @return array
     */
    public function getOverdueSum()
    {
        $row = "(`order`.principal + `order`.principal * `order`.loan_days * `order`.daily_rate + 
    `order`.principal * (CASE WHEN order.paid_time > '2020-04-01' THEN 0 ELSE overdue_days END) * overdue_rate * (1 + order_detail.value)
    ) * repayment_plan.repay_proportion/100
    - if(repayment_plan.part_repay_amount, repayment_plan.part_repay_amount, 0)";
        list($todaySum, $chainRatioByYesterdaySum, $chainRatioByweekdaySum) = $this->getChainRatioByModel(
            Order::leftJoin('repayment_plan', function ($join) {
                $join->on('repayment_plan.order_id', 'order.id');
                $join->where('repayment_plan.installment_num', 1);
            })
                ->leftJoin('order_detail', function ($join) {
                    $join->on('order_detail.order_id', 'order.id');
                    $join->where('order_detail.key', OrderDetail::KEY_GST_PENALTY_RATE);
                })
                ->whereIn('order.status', Order::STATUS_NOT_COMPLETE)
                ->whereIn('repayment_plan.status', RepaymentPlan::UNFINISHED_STATUS),
            'overdue_time',
            DB::raw($row));

        $promisePaidSum = Collection::leftjoin('order', 'collection.order_id', '=', 'order.id')
            ->leftJoin('repayment_plan', function ($join) {
                $join->on('repayment_plan.order_id', 'order.id');
                $join->where('repayment_plan.installment_num', 1);
            })
            ->leftJoin('order_detail', function ($join) {
                $join->on('order_detail.order_id', 'order.id');
                $join->where('order_detail.key', OrderDetail::KEY_GST_PENALTY_RATE);
            })
            ->whereHas('collectionRecord', function ($collectionRecord) {
                return $collectionRecord->whereBetween('promise_paid_time', [$this->todayTimeStart, $this->todayTimeEnd]);
            })
            ->whereIn('order.status', Order::STATUS_NOT_COMPLETE)
            //->whereBetween('repayment_plan.appointment_paid_time', [$this->yesterdayTimeStart, $this->yesterdayTimeEnd])
            ->sum(DB::raw($row));
        $promisePaidSum = StatisticsHelper::numberFormat($promisePaidSum);

        return [
            StatisticsHelper::numberFormat($todaySum),
            $chainRatioByYesterdaySum,
            $chainRatioByweekdaySum,
            t('承诺应还金额    ¥', 'statistics') . "{$promisePaidSum}",
        ];
    }

    /**
     * 当日入催环比
     *
     * @return array
     */
    public function getCollectionChainRatioCountSum($isSum = false)
    {
        $todayConutSum = $this->getSumByCollection($this->todayTimeStart, $this->todayTimeNow, $isSum);
        $yesterdayConutSum = $this->getSumByCollection($this->yesterdayTimeStart, $this->yesterdayTimeNow, $isSum);
        $weekdayConutSum = $this->getSumByCollection($this->weekdayTimeStart, $this->weekdayTimeNow, $isSum);

        return [
            $todayConutSum,
            StatisticsHelper::chainRatio($todayConutSum, $yesterdayConutSum),
            StatisticsHelper::chainRatio($todayConutSum, $weekdayConutSum),
        ];
    }

    /**
     * 当日入催环比查询
     *
     * @param $TimeStart
     * @param $TimeEnd
     * @return mixed
     */
    public function getSumByCollection($TimeStart, $TimeEnd, $isSum)
    {
        $query = Collection::model()
            ->whereBetween('collection.created_at', [$TimeStart, $TimeEnd]);
        if ($isSum) {
            return $query
                ->leftJoin('order', 'collection.order_id', '=', 'order.id')
                ->sum(DB::raw('`order`.`principal`+`order`.`principal`*`order`.`daily_rate`'));
        }
        return $query->count();
    }

    /**
     * 入催笔数
     *
     * @return array
     */
    public function getOverdueCount()
    {
        list($todayCount, $chainRatioByYesterdaySum, $chainRatioByweekdaySum) = $this->getCollectionChainRatioCountSum();
        $yesterdayNeedRepayCount = RepaymentPlan::model()
            ->whereBetween('appointment_paid_time', [DateHelper::subDays(1), DateHelper::date()])
            ->count();
        $this->todayNewCollectionCount = $todayCount;
        $yesterdayCollectionPercent = StatisticsHelper::percent($todayCount, $yesterdayNeedRepayCount);
        return [
            $todayCount,
            $chainRatioByYesterdaySum,
            $chainRatioByweekdaySum,
            t('入催率', 'statistics') . "    {$yesterdayCollectionPercent}%",
        ];
    }

    /**
     * 承诺还款笔数
     *
     * @return array
     */
    public function getPromisePaidCount()
    {

        $callFun = function ($model) {
            $modelData = $model->first();
            return $modelData->count ?? 0;
        };
        list($todayCount, $chainRatioByYesterdaySum, $chainRatioByweekdaySum) = $this->getChainRatioByModel(
            Collection::leftJoin('collection_record', 'collection_record.collection_id', '=', 'collection.id')
                ->select(DB::raw('count(DISTINCT collection_record.order_id) as count')),
            'collection_record.promise_paid_time', '', $callFun);

        $promisePaidRepayCount = Collection::leftJoin('collection_record', 'collection_record.collection_id', '=', 'collection.id')
            ->select(DB::raw('count(DISTINCT collection_record.order_id) as count'))
            ->whereBetween('collection.finish_time', [$this->todayTimeStart, $this->todayTimeNow])
            ->whereBetween('collection_record.promise_paid_time', [$this->todayTimeStart, $this->todayTimeNow])
            ->first()->count;
        $promisePaidRepayPercent = StatisticsHelper::percent($promisePaidRepayCount, $todayCount);

        return [
            $todayCount,
            $chainRatioByYesterdaySum,
            $chainRatioByweekdaySum,
            t('承诺还款率', 'statistics') . "    {$promisePaidRepayPercent}%",
        ];
    }

    /**
     * 催收成功笔数
     *
     * @return array
     */
    public function getCollectionSuccessCount()
    {
        list($todayCount, $chainRatioByYesterdaySum, $chainRatioByweekdaySum) = $this->getChainRatioByModel(new Collection(), ['created_at', 'finish_time']);

        $todayToCollectionCount = Collection::model()->whereBetween('created_at', [$this->todayTimeStart, $this->todayTimeNow])->count();
        $todayToCollectionAndFinishCount = Collection::model()
            ->whereBetween('created_at', [$this->todayTimeStart, $this->todayTimeNow])
            ->whereBetween('finish_time', [$this->todayTimeStart, $this->todayTimeNow])
            ->count();
        $todayToCollectionAndFinishPercent = StatisticsHelper::percent($todayToCollectionAndFinishCount, $todayToCollectionCount);

        return [
            $todayCount,
            $chainRatioByYesterdaySum,
            $chainRatioByweekdaySum,
            t('催回率', 'statistics') . "    {$todayToCollectionAndFinishPercent}%",
        ];
    }

    /**
     * 已坏账数
     *
     * @return array
     */
    public function getCollectionBadCount()
    {
        list($todaySum, $chainRatioByYesterdaySum, $chainRatioByweekdaySum) = $this->getChainRatioByModel(Collection::where('status', Collection::STATUS_COLLECTION_BAD), 'bad_time');

        $collectionCount = Collection::model()->count();
        $collectionBadCount = Collection::model()->where('status', Collection::STATUS_COLLECTION_BAD)->count();
        $collectionBadPercent = StatisticsHelper::percent($collectionBadCount, $collectionCount);

        return [
            $todaySum,
            $chainRatioByYesterdaySum,
            $chainRatioByweekdaySum,
            t('坏账率', 'statistics') . "    {$collectionBadPercent}%",
        ];
    }

    /**
     * 催记次数
     *
     * @return array
     */
    public function getCollectionRecordCount()
    {
        $callFun = function ($model) {
            $modelData = $model->first();
            return $modelData->count ?? 0;
        };
        list($todayCount, $chainRatioByYesterdayCount, $chainRatioByweekdayCount) = $this->getChainRatioByModel(CollectionRecord::select(DB::raw('count(DISTINCT collection_record.order_id) as count')), 'created_at', '', $callFun);

        $todayCollectionRecordOrderCount = Collection::model()->whereBetween('collection_time', [$this->todayTimeStart, $this->todayTimeNow])->count();
        $todayCollectionCount = Collection::model()->whereBetween('created_at', [$this->todayTimeStart, $this->todayTimeNow])->count();
        $todayCollectionRecordPercent = StatisticsHelper::percent($todayCollectionRecordOrderCount, $todayCollectionCount);

        return [
            $todayCount,
            $chainRatioByYesterdayCount,
            $chainRatioByweekdayCount,
            t('催记率', 'statistics') . "    {$todayCollectionRecordPercent}%",
        ];
    }

    /**
     * 总审批数
     *
     * @return array
     */
    public function totalCount(): array
    {
        list($todaySum, $chainRatioByYesterdaySum, $chainRatioByweekdaySum) = $this->getChainRatioByModel(Order::query(), ['created_at', 'system_time']);
        $todayNewUserApprove = $this->getCountSumByModel(Order::query()->where('quality', Order::QUALITY_NEW), ['system_time', 'created_at'], $this->todayTimeStart, $this->todayTimeNow);
        $todayOldUserApprove = $this->getCountSumByModel(Order::query()->where('quality', Order::QUALITY_OLD), ['system_time', 'created_at'], $this->todayTimeStart, $this->todayTimeNow);

        return [
            $todaySum,
            $chainRatioByYesterdaySum,
            $chainRatioByweekdaySum,
            " {$todayNewUserApprove}/ {$todayOldUserApprove}" . t('（新/复贷）', 'statistics'),
        ];

    }

    /**
     * 机审通过数
     *
     * @return array
     */
    public function systemPass(): array
    {
        // 进入审批池的单都是机审通过的单
        list($todaySum, $chainRatioByYesterdaySum, $chainRatioByweekdaySum) = $this->getChainRatioByModel(ApprovePool::query(), ['created_at']);
        // 当天申请的所有订单
        $total = $this->getCountSumByModel(Order::query(), ['system_time', 'created_at'], $this->todayTimeStart, $this->todayTimeNow);
        $todaySystemSuccessPercent = StatisticsHelper::percent($todaySum, $total);

        return [
            $todaySum,
            $chainRatioByYesterdaySum,
            $chainRatioByweekdaySum,
            t('机审通过转化率') . " {$todaySystemSuccessPercent} %",
        ];
    }

    /**
     * 入初审数
     *
     * @return array
     */
    public function manualCount(): array
    {
        return $this->getCountByType(ApprovePool::ORDER_FIRST_GROUP);
    }

    /**
     * 获取初审/电审入审单量
     *
     * @param $type
     * @return array
     */
    protected function getCountByType($type)
    {
        $query0 = ApprovePoolLog::where('type', $type);
        list($todaySum, $chainRatioByYesterdaySum, $chainRatioByweekdaySum) = $this->getChainRatioByModel($query0, ['created_at']);

        $query1 = $this->getApprovePoolLogRelation()
            ->where('approve_pool_log.type', $type);
        $query2 = clone $query1;

        $todayNewUserApprove = $this->getCountSumByModel($query1->where('order.quality', Order::QUALITY_NEW), ['order.created_at'], $this->todayTimeStart, $this->todayTimeNow);
        $todayOldUserApprove = $this->getCountSumByModel($query2->where('order.quality', Order::QUALITY_OLD), ['order.created_at'], $this->todayTimeStart, $this->todayTimeNow);

        return [
            $todaySum,
            $chainRatioByYesterdaySum,
            $chainRatioByweekdaySum,
            " {$todayNewUserApprove}/ {$todayOldUserApprove}" . t('（新/复贷）', 'statistics'),
        ];
    }

    /**
     * approve_pool_log和order关联关系
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getApprovePoolLogRelation()
    {
//        return \DB::connection('cash-now-approve')
//            ->table('approve_pool_log')
        return \DB::table('approve_pool_log')
            ->join('approve_pool', 'approve_pool_log.approve_pool_id', 'approve_pool.id')
            ->join(getDatabaseName((new Order)->getConnectionName()) . '.order', 'approve_pool.id', 'order.id');
    }

    /**
     * 等待初审数量
     *
     * @return array
     */
    public function waitManualApprove(): array
    {
        return $this->getWaitCountByType(ApprovePool::ORDER_FIRST_GROUP);
    }

    /**
     * 获取初审/电审等待审批单量
     *
     * @param $type
     * @return array
     */
    protected function getWaitCountByType($type)
    {
        $query0 = ApprovePool::where('status', ApprovePool::STATUS_WAITING)
            ->where('type', $type);
        list($todaySum, $chainRatioByYesterdaySum, $chainRatioByweekdaySum) = $this->getChainRatioByModel($query0, ['created_at']);

        $query1 = $this->getApprovePoolRelation()
            ->where('approve_pool.type', $type)
            ->where('approve_pool.status', ApprovePool::STATUS_WAITING);
        $query2 = clone $query1;

        $todayNewUserApprove = $this->getCountSumByModel($query1->where('order.quality', Order::QUALITY_NEW), ['order.created_at'], $this->todayTimeStart, $this->todayTimeNow);
        $todayOldUserApprove = $this->getCountSumByModel($query2->where('order.quality', Order::QUALITY_OLD), ['order.created_at'], $this->todayTimeStart, $this->todayTimeNow);

        return [
            $todaySum,
            $chainRatioByYesterdaySum,
            $chainRatioByweekdaySum,
            " {$todayNewUserApprove}/ {$todayOldUserApprove}" . t('（新/复贷）', 'statistics'),
        ];
    }

    /**
     * approve_pool和order关联关系
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getApprovePoolRelation()
    {
//        return DB::connection('cash-now-approve')
//            ->table('approve_pool')
        return DB::table('approve_pool')
            ->join(getDatabaseName((new Order)->getConnectionName()) . '.order', 'approve_pool.id', 'order.id');
    }

    /**
     * 初审通过量
     *
     * @return array
     */
    public function manualPass(): array
    {
        return $this->getPassCountByType(ApprovePool::ORDER_FIRST_GROUP);
    }

    /**
     * 获取初审/电审等待审批单量
     *
     * @param $type
     * @return array
     */
    protected function getPassCountByType($type)
    {
        $query0 = ApprovePoolLog::where('type', $type)
            ->where('result', ApprovePoolLog::FIRST_APPROVE_RESULT_PASS);
        list($todaySum, $chainRatioByYesterdaySum, $chainRatioByweekdaySum) = $this->getChainRatioByModel($query0, ['created_at']);

        $query1 = $this->getApprovePoolLogRelation()
            ->where('approve_pool_log.type', $type);
        $total = $this->getCountSumByModel($query1, ['order.created_at'], $this->todayTimeStart, $this->todayTimeNow);
        $todayManualPassPercent = StatisticsHelper::percent($todaySum, $total);

        return [
            $todaySum,
            $chainRatioByYesterdaySum,
            $chainRatioByweekdaySum,
            t('初审通过转化率', 'statistics') . " {$todayManualPassPercent} %",
        ];
    }

    /**
     * 入电审数量
     *
     * @return array
     */
    public function callCount(): array
    {
        return $this->getCountByType(ApprovePool::ORDER_CALL_GROUP);
    }

    /**
     * 等待电审数量
     *
     * @return array
     */
    public function waitCallApprove(): array
    {
        return $this->getWaitCountByType(ApprovePool::ORDER_CALL_GROUP);
    }

    /**
     * 电审通过数量
     *
     * @return array
     */
    public function callPass(): array
    {
        return $this->getPassCountByType(ApprovePool::ORDER_CALL_GROUP);
    }

    /**
     * 待签约数量
     *
     * @return array
     */
    public function waitSign(): array
    {
        $query0 = Order::whereIn('status', Order::WAIT_SIGN);
        $query1 = clone $query0;
        $query2 = clone $query0;
        list($todaySum, $chainRatioByYesterdaySum, $chainRatioByweekdaySum) = $this->getChainRatioByModel($query0, ['pass_time']);
        $query1->where('quality', Order::QUALITY_NEW);
        $query2->where('quality', Order::QUALITY_OLD);
        $todayNewUserWaitSign = $this->getCountSumByModel($query1, ['pass_time'], $this->todayTimeStart, $this->todayTimeNow);
        $todayOldUserWaitSign = $this->getCountSumByModel($query2, ['pass_time'], $this->todayTimeStart, $this->todayTimeNow);

        return [
            $todaySum,
            $chainRatioByYesterdaySum,
            $chainRatioByweekdaySum,
            " {$todayNewUserWaitSign}/ {$todayOldUserWaitSign}" . t('（新/复贷）', 'statistics'),
        ];
    }

    /**
     * 已签约数量
     *
     * @return array
     */
    public function signContract(): array
    {
        $query0 = Order::whereNotNull('signed_time');
        list($todaySum, $chainRatioByYesterdaySum, $chainRatioByweekdaySum) = $this->getChainRatioByModel($query0, ['signed_time']);
        // 通过量
        $query1 = Order::whereIn('status', [Order::STATUS_MANUAL_PASS, Order::STATUS_SYSTEM_PASS]);
        $todayPassCount = $this->getCountSumByModel($query1, ['pass_time'], $this->todayTimeStart, $this->todayTimeNow);

        $todaySignSucessPercent = StatisticsHelper::percent($todaySum, $todayPassCount);

        return [
            $todaySum,
            $chainRatioByYesterdaySum,
            $chainRatioByweekdaySum,
            t('签约完成转化率', 'statistics') . " {$todaySignSucessPercent}% ",
        ];
    }
}
