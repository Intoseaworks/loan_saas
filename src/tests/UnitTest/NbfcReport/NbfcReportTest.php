<?php

namespace Tests\UnitTest\NbfcReport;

use Api\Models\Order\Order;
use Common\Events\Report\NbfcReportEvent;
use Common\Models\Nbfc\NbfcReportConfig;
use Tests\TestCase;

class NbfcReportTest extends TestCase
{
    public function testEventReport()
    {
        $order = Order::query()->find(875);

        event(new NbfcReportEvent($order->id, NbfcReportConfig::REPORT_NODE_SIGN));
        dd('sign');

        $res = event(new NbfcReportEvent($order->id, NbfcReportConfig::REPORT_NODE_REMIT));
        dd('remit', $res);

        $res = event(new NbfcReportEvent($order->id, NbfcReportConfig::REPORT_NODE_REPAY));
        dd('repay', $res);
    }
}
