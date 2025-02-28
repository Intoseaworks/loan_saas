<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/31
 * Time: 15:42
 */

namespace Common\Console\Commands\Order;

use Carbon\Carbon;
use Common\Events\Order\OrderFlowPushEvent;
use Common\Models\Order\Order;
use Illuminate\Console\Command;


class OrderExpireRemind extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:expire-remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '订单（到期、逾期）还款提醒';


    public function handle()
    {
        /** 到期还款提醒 **/
        $this->comingRemind();

        /** 逾期还款提醒 **/
        $this->overdueRmind();
    }

    /**
     * 当还款日期在今天和明天时发送提醒
     */
    private function comingRemind()
    {
        $timeStart = Carbon::today()->toDateTimeString();
        $timeEnd = Carbon::today()->addDays(2)->toDateTimeString();
        $with = 'repaymentPlans';
        $ordersComing = Order::select('order.*')->with($with)->whereIn('status', [
            Order::STATUS_SYSTEM_PAID,
            Order::STATUS_MANUAL_PAID,
            Order::STATUS_REPAYING,
        ])->whereHas($with, function ($query) use ($timeStart, $timeEnd) {
            $query->where('appointment_paid_time', '>', $timeStart);
            $query->where('appointment_paid_time', '<', $timeEnd);
        })->get();

        foreach ($ordersComing as $order) {
            event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_EXPIRATION_REMINDER));
        }
    }

    private function overdueRmind()
    {
        $ordersOverdue = Order::select('order.*')->whereIn('status', [
            Order::STATUS_OVERDUE,
        ])->get();

        foreach ($ordersOverdue as $order) {
            event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_OVERDUE));
        }
    }


}