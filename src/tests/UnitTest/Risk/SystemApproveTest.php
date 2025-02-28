<?php

namespace Tests\UnitTest\Risk;

use Common\Console\Services\Risk\SystemApproveServer;
use Common\Models\Order\Order;
use Common\Models\SystemApprove\SystemApproveTask;
use Common\Utils\MerchantHelper;
use Tests\Admin\TestBase;

class SystemApproveTest extends TestBase
{
    public function testApprove()
    {
        $order = Order::with([])->where('id', 923)->first(); // 923 985

        MerchantHelper::setMerchantId($order->app_id);

        $res = SystemApproveServer::server()->approve($order);

        dd($res);
    }

    public function testApproveExec()
    {
        $order = Order::with([])->where('id', 923)->first(); // 923 985
        $task = SystemApproveTask::query()->where('order_id', $order->id)->latest()->first();

        MerchantHelper::setMerchantId($order->app_id);

        $res = SystemApproveServer::server()->approveExec($task);

        dd($res);
    }
}
