<?php

namespace Tests\Admin\Order;

use Common\Events\Order\OrderFlowPushEvent;
use Common\Models\Order\Order;
use Tests\Admin\TestBase;

/**
 * Class OrderFlowPushTest
 * 订单状态流转 推送app
 */
class OrderFlowPushTest extends TestBase
{
    /**
     * 订单列表
     */
    public function testPush()
    {

        $type = OrderFlowPushEvent::TYPE_WAIT_LOAN;

        $result = event(new OrderFlowPushEvent(null, $type, 2));

        dd($result);
    }

    public function testP()
    {
        $order = Order::find(153);

        $type = OrderFlowPushEvent::TYPE_APPROVE_PASS;

        $result = event(new OrderFlowPushEvent($order, $type));

        dd($result);
    }
}