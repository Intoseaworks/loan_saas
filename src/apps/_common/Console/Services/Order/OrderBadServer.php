<?php

namespace Common\Console\Services\Order;

use Admin\Models\Order\Order;
use Carbon\Carbon;
use Common\Events\Risk\RiskDataSendEvent;
use Common\Models\Collection\Collection;
use Common\Models\Collection\CollectionLog;
use Common\Models\Order\RepaymentPlan;
use Common\Redis\CollectionStatistics\CollectionStatisticsRedis;
use Common\Services\BaseService;
use Common\Services\Order\OrderServer;
use Common\Utils\Email\EmailHelper;
use Illuminate\Support\Facades\DB;

class OrderBadServer extends BaseService
{
    public function orderToBad()
    {
        $overdueRepaymentPlans = OrderServer::server()->beCollectionBadOrders();
        $success = 0;
        $count = $overdueRepaymentPlans->count();
        $overdueRepaymentPlans = $overdueRepaymentPlans->get();
        $successOrders = [];
        $overdueRepaymentPlans->load('order.collection');

        foreach ($overdueRepaymentPlans as $repaymentPlan) {
            /** @var $repaymentPlan RepaymentPlan */
            /** 坏账需要清理历史减免 */
            $this->handleBad($repaymentPlan);
            $success++;
            $successOrders[] = $repaymentPlan->order;

            event(new RiskDataSendEvent($repaymentPlan->user_id, RiskDataSendEvent::NODE_ORDER_BAD));
        }

        //催收统计
        foreach ($successOrders as $successOrder) {
            $collection = optional($successOrder)->collection;
            //催收人员统计++
            if ($collection) {
                CollectionStatisticsRedis::redis()->hIncr(
                    $collection->admin_id,
                    CollectionStatisticsRedis::FIELD_STAFF_COLLECTION_BAD,
                    $successOrder->merchant_id
                );
            }

            //催收坏账统计 ++
            CollectionStatisticsRedis::redis()->incr(CollectionStatisticsRedis::KEY_COLLECTION_BAD_COUNT, $successOrder->merchant_id);
        }

        if ($count > 0) {
            EmailHelper::send("成功{$success}/{$count}", '流转坏账状态');
        }
        return $this->outputSuccess('订单流转坏账成功');
    }

    /**
     * 处理坏账单一逻辑
     * @param RepaymentPlan $repaymentPlan
     * @throws \Exception
     */
    public function handleBad(RepaymentPlan $repaymentPlan)
    {
        DB::beginTransaction();
        if (!$repaymentPlan->clearReGGduction()) {
            DB::rollBack();
        }
        if (!OrderServer::server()->collectionBad($repaymentPlan->order->id)) {
            DB::rollBack();
        }
        $collection = Collection::model()->whereOrderId($repaymentPlan->order->id)->first();
        if ($collection) {
            $fromStatus = $collection->status;
            if (!$collection->setScenario([
                    'bad_time' => Carbon::now()->toDateTimeString(),
                    'status' => Collection::STATUS_COLLECTION_BAD
                ]
            )->save()) {
                DB::rollBack();
            }
            (new CollectionLog)->addLog($collection, $fromStatus, Collection::STATUS_COLLECTION_BAD);
        }
        DB::commit();
    }

    /**
     * 撤销坏账
     * @param $order
     * @return bool
     */
    public function recallBad($order)
    {
        /** @var Order $order */
        return OrderServer::server()->recallCollectionBad($order->id);
    }
}
