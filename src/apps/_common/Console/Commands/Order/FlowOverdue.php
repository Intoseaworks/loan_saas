<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Order;


use Common\Events\Order\OrderFlowPushEvent;
use Common\Events\Risk\RiskDataSendEvent;
use Common\Models\Order\Order;
use Common\Models\Order\RepaymentPlan;
use Common\Redis\CollectionStatistics\CollectionStatisticsRedis;
use Common\Services\Order\OrderServer;
use Common\Utils\Email\EmailHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FlowOverdue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:flow-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '流转逾期状态';

    public function handle()
    {
        MerchantHelper::callback(function () {
            /** 流转逾期状态 */
            $this->toOverdueStatus();
        });
    }

    protected function toOverdueStatus()
    {
        $repaymentPlans = OrderServer::server()->beOverdueOrders();
        $count = $repaymentPlans->count();
        $success = $fail = 0;
        foreach ($repaymentPlans->get() as $repaymentPlan) {
            /** @var $repaymentPlan RepaymentPlan */
            $order = $repaymentPlan->order;
            $fromStatus = $order->status;
            DB::beginTransaction();
            $orderServer = OrderServer::server();
            $orderServer->beOverdue($order->id);
            //$overdueDays = $orderServer->getOverdueDays($order->loan_days, $order->paid_time);
            $overdueDays = $order->getOverdueDays();
            echo "Order:{$order->id}|paid_time:{$order->paid_time}|loan_days:{$order->loan_days}|overdue_days:{$overdueDays}" . PHP_EOL;
            /*if (!$repaymentPlan->setScenario([
                'overdue_days' => $overdueDays,
                'overdue_fee' => $order->overdueFee(),
            ])->save()) {
                DB::rollBack();
            }*/
            $success++;
            DB::commit();

            event(new RiskDataSendEvent($order->user_id, RiskDataSendEvent::NODE_ORDER_OVERDUE));

            if ($fromStatus != Order::STATUS_OVERDUE) {
                //催收统计 逾期计数 ++
                CollectionStatisticsRedis::redis()->incr(CollectionStatisticsRedis::KEY_OVERDUE_COUNT, $order->merchant_id);
                //逾期 通知推送
                event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_OVERDUE));
            }
        }
        if ($count > 0) {
            EmailHelper::send("成功{$success}/{$count}", '流转逾期状态');
        }
    }
}
