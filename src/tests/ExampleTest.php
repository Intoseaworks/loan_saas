<?php

namespace Tests;

use Common\Models\Order\Order;
use Common\Services\Risk\RiskStrategyServer;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $order = Order::find(46722);
        $server = RiskStrategyServer::server()->getDataByRulesPlatform($order, 1);
        /** 请求成功 */
        if ($server->isSuccess()) {
            list($skipRiskControl2, $skipManualApproval, $rejectCode, $strategyResult) = $server->getData();
            dd($rejectCode);
        }

        $this->get('/');

        $this->assertEquals(
            $this->app->version(), $this->response->getContent()
        );
    }
}
