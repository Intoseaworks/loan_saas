<?php

use Common\Models\Order\Order;
use Common\Models\Trade\TradeLog;
use Common\Services\Order\OrderServer;
use Tests\Admin\TestBase;

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/10
 * Time: 21:25
 */
class OrderTest extends TestBase
{
    /**
     * 订单列表
     */
    public function testOrderList()
    {
        $params = [
        ];
        $this->json('GET', '/api/order/index', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();

        $params['export'] = 1;
        $this->json('GET', '/api/order/index', $params);
        $this->assertResponseOk();
    }

    public function testPaidToSign()
    {
        dd('s');
        $tradeLog = TradeLog::query()->where('trade_platform', 'paytm')
            ->where('created_at', '>', '2020-01-28')
            ->where('trade_result', '2')
            ->get();

        $order = Order::query()->whereIn('id', $tradeLog->pluck('master_related_id')->toArray())
            ->where('status', Order::STATUS_SYSTEM_PAY_FAIL)
            ->get();

        $count = 0;
        $ids = [];
        foreach ($order as $o) {
            if ($o->status == Order::STATUS_SYSTEM_PAY_FAIL) {
                OrderServer::server()->manualPayFailToManualPass($o->id);
                $count++;
                $ids[] = $o->id;
            }
        }

        dd($count, $ids, implode("\n", $ids));
    }
}
