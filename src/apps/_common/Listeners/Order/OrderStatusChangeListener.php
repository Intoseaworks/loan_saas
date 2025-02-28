<?php

namespace Common\Listeners\Order;

use Common\Events\Order\OrderStatusChangeEvent;
use Common\Models\Order\Order;
use Common\Models\Order\OrderLog;
use Common\Utils\LoginHelper;

class OrderStatusChangeListener
{


    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function handle(OrderStatusChangeEvent $event)
    {
        $order = $event->order;

        # 电二审2次
        if ($order->status == $event->toStatus && $event->toStatus != Order::STATUS_WAIT_TWICE_CALL_APPROVE) {
            return true;
        }
        OrderLog::model(OrderLog::SCENARIO_CREATE)
            ->saveModel([
                'merchant_id' => $order->merchant_id,
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'admin_id' => LoginHelper::getAdminId(),
                'from_status' => $order->status,
                'to_status' => $event->toStatus,
                'content' => $event->content,
                'name' => $event->name,
            ]);

        return true;
    }
}
