<?php

namespace Common\Services\CollectionStatistics;

use Admin\Services\Collection\CollectionServer;
use Admin\Services\Staff\StaffServer;
use Carbon\Carbon;
use Common\Models\CollectionStatistics\StatisticsCollection;
use Common\Models\CollectionStatistics\StatisticsCollectionRate;
use Common\Models\CollectionStatistics\StatisticsCollectionStaff;
use Common\Models\Order\Order;
use Common\Redis\CollectionStatistics\CollectionStatisticsRedis;
use Common\Services\BaseService;
use Common\Services\Order\OrderServer;
use Common\Utils\MerchantHelper;
use Common\Models\CollectionStatistics\StatisticsCollectionStaffAchievement;
use Common\Models\CollectionStatistics\StatisticsCollectionStaffPeso;
use Common\Models\CollectionStatistics\StatisticsCollectionStaffOnline;
use Admin\Services\Collection\CollectionOnlineServer;

class CollectionStatisticsServer extends BaseService
{
    /**
     * 催收订单统计
     * @param Carbon $date
     * @suppress PhanTypeMismatchArgumentInternal
     * @return mixed
     */
    public function statistics(Carbon $date)
    {
        $dateString = $date->toDateString();
        $yesterdayDateString = $date->copy()->subDay()->toDateString();

        //历史数据
        $statisticsCollection = StatisticsCollection::getByDate($yesterdayDateString);
        //订单数
        $dataOverdueCount = $statisticsCollection->overdue_count ?? 0;
        //成功数
        $dataOverdueFinishCount = $statisticsCollection->overdue_finish_count ?? 0;
        //坏账数
        $dataCollectionBadCount = $statisticsCollection->collection_bad_count ?? 0;

        //今日新增订单数
        $todayOverdue = CollectionStatisticsRedis::redis()->get(CollectionStatisticsRedis::KEY_OVERDUE_COUNT, $dateString);
        //今日承诺还款订单数
        $todayPromisePaid = CollectionStatisticsRedis::redis()->get(CollectionStatisticsRedis::KEY_PROMISE_PAID_COUNT, $dateString);
        //今日成功订单数(逾期结清)
        $todayOverdueFinish = CollectionStatisticsRedis::redis()->get(CollectionStatisticsRedis::KEY_OVERDUE_FINISH_COUNT, $dateString);
        //今日坏账订单数(已坏账)
        $todayCollectionBad = CollectionStatisticsRedis::redis()->get(CollectionStatisticsRedis::KEY_COLLECTION_BAD_COUNT, $dateString);
        //今日催回率
        $collectionSuccessRate = $todayOverdue ? bcdiv($todayOverdueFinish, $todayOverdue, 2) : 0;

        //累计订单数
        $overdueCount = bcadd($dataOverdueCount, $todayOverdue);
        //累计成功数(逾期结清)
        $overdueFinishCount = bcadd($dataOverdueFinishCount, $todayOverdueFinish);
        //累计坏账数(已坏账)
        $collectionBadCount = bcadd($dataCollectionBadCount, $todayCollectionBad);
        //累计回款率
        $collectionSuccessCountRate = $overdueCount ? bcdiv($overdueFinishCount, $overdueCount, 2) : 0;

        //截止当前回款成功数
        $realOverdueFinish = OrderServer::server()->getCountByStatus(Order::STATUS_OVERDUE_FINISH);

        //新增or更新统计数据
        return StatisticsCollection::updateOrCreate([
            'date' => $dateString,
            'merchant_id' => MerchantHelper::getMerchantId(),
        ], [
            'overdue_count' => $overdueCount,
            'overdue_finish_count' => $overdueFinishCount,
            'collection_bad_count' => $collectionBadCount,
            'collection_success_count_rate' => $collectionSuccessCountRate,
            'today_overdue' => $todayOverdue,
            'today_promise_paid' => $todayPromisePaid,
            'today_overdue_finish' => $todayOverdueFinish,
            'today_collection_bad' => $todayCollectionBad,
            'real_overdue_finish' => $realOverdueFinish,
            'collection_success_rate' => $collectionSuccessRate,
        ]);
    }

    /**
     * 催回率统计
     * @param Carbon $date
     * @suppress PhanTypeMismatchArgumentInternal
     * @return mixed
     */
    public function statisticsRate(Carbon $date)
    {
        $dateString = $date->toDateString();
        $yesterdayDateString = $date->copy()->subDay()->toDateString();

        //历史数据
        $statisticsCollectionRate = StatisticsCollectionRate::getByDate($yesterdayDateString);
        //订单数
        $dataOverdueCount = $statisticsCollectionRate->history_overdue_count ?? 0;
        //坏账数
        $dataCollectionBadCount = $statisticsCollectionRate->history_collection_bad_count ?? 0;

        //当前逾期订单数(统计时处于逾期状态的订单数)
        $presentOverdueCount = OrderServer::server()->getCountByStatus(Order::STATUS_OVERDUE);
        //截止当前回款成功数(统计时处于逾期结清的订单数)
        $presentOverdueFinishCount = OrderServer::server()->getCountByStatus(Order::STATUS_OVERDUE_FINISH);

        //今日新增订单数
        $todayOverdue = CollectionStatisticsRedis::redis()->get(CollectionStatisticsRedis::KEY_OVERDUE_COUNT, $dateString);
        //今日成功订单数(逾期结清)
        $todayOverdueFinish = CollectionStatisticsRedis::redis()->get(CollectionStatisticsRedis::KEY_OVERDUE_FINISH_COUNT, $dateString);
        //今日坏账订单数(已坏账)
        $todayCollectionBadCount = CollectionStatisticsRedis::redis()->get(CollectionStatisticsRedis::KEY_COLLECTION_BAD_COUNT, $dateString);
        //催收记录次数  改用催收人员 催收次数总和
        //$collectionCount = CollectionStatisticsRedis::redis()->get(CollectionStatisticsRedis::KEY_COLLECTION_COUNT, $dateString);
        $collectionCount = CollectionStatisticsRedis::redis()->statisticsCollectCount($dateString);
        //当日回款率 [当日回款成功数]/([当前逾期订单数]+[当日回款成功数])
        $presentOverdueSum = bcadd($presentOverdueCount, $todayOverdueFinish);
        $todayCollectionRate = $presentOverdueSum ? bcdiv($todayOverdueFinish, $presentOverdueSum, 2) : 0;

        //逾期总订单数(进入过逾期的订单数)
        $historyOverdueCount = bcadd($dataOverdueCount, $todayOverdue);
        //历史坏账数(已坏账)
        $historyCollectionBadCount = bcadd($dataCollectionBadCount, $todayCollectionBadCount);

        //截止当前回款率 [截止当前回款成功数]/[逾期总订单数]
        $historyCollectionRate = $historyOverdueCount ? bcdiv($presentOverdueFinishCount, $historyOverdueCount, 2) : 0;

        //新增or更新催回率统计数据
        return StatisticsCollectionRate::updateOrCreate([
            'date' => $dateString,
            'merchant_id' => MerchantHelper::getMerchantId(),
        ], [
            'history_overdue_count' => $historyOverdueCount,
            'present_overdue_count' => $presentOverdueCount,
            'today_overdue_finish' => $todayOverdueFinish,
            'present_overdue_finish_count' => $presentOverdueFinishCount,
            'today_collection_bad_count' => $todayCollectionBadCount,
            'history_collection_bad_count' => $historyCollectionBadCount,
            'today_collection_rate' => $todayCollectionRate,
            'history_collection_rate' => $historyCollectionRate,
            'collection_count' => $collectionCount,
        ]);
    }

    /**
     * 催收员每日统计
     * @param Carbon $date
     * @return mixed
     */
    public function statisticsStaff($date)
    {
        echo $dateString = $date->toDateString();
        $data = StatisticsCollectionStaffAchievement::model()->search(['date' => [$dateString,$dateString]])->get()->toArray();
        StatisticsCollectionStaffPeso::model()->where('date',$dateString)->delete();
        foreach($data as $item){
            $item = (array)$item;
            unset($item['today_assign_finish_rate']);
            unset($item['finish_rate']);
            unset($item['repay_finish_rate']);
            unset($item['repay_rate']);
            unset($item['bad_rate']);
            $item['merchant_id'] = MerchantHelper::getMerchantId();
            $item['updated_at'] = date("Y-m-d H:i:s");
            StatisticsCollectionStaffPeso::model()->updateOrCreateModel($item, [
                "date" => $item['date'],
                "level" => $item['level'],
                "admin_id" => $item['admin_id'],
                "username" => $item['username'],
                'merchant_id' => $item['merchant_id']
            ]);
        }
    }
    
    public function statisticsOnline(){
        $merchantId = MerchantHelper::getMerchantId();
        $data = CollectionOnlineServer::server()->report([])->get()->toArray();
        $data = json_decode(json_encode($data), true);
        \DB::delete("delete from statistics_collection_staff_online where merchant_id=?", [$merchantId]);
        foreach($data as $item){
            $item['merchant_id'] = $merchantId;
            $item['updated_at'] = date("Y-m-d H:i:s");
            StatisticsCollectionStaffOnline::model()->updateOrCreateModel($item, [
                "admin_id" => $item['admin_id'],
                "level_name" => $item['level_name'],
                'merchant_id' => $item['merchant_id']
            ]);
        }
    }
}
