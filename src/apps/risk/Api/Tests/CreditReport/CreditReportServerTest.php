<?php

namespace Risk\Api\Tests\CreditReport;

use Common\Utils\MerchantHelper;
use Risk\Api\Tests\TestBase;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Services\CreditReport\CreditReportServer;

class CreditReportServerTest extends TestBase
{
    public function testHmCreditReport()
    {
        $order = Order::with([])->where('id', 923)->first();

        MerchantHelper::setMerchantId($order->app_id);

        $result = CreditReportServer::server()->hmCreditReport($order);
        dd($result);
    }

    public function testExperianCreditReport()
    {
        $order = Order::with([])->where('id', 923)->first();

        MerchantHelper::setMerchantId($order->app_id);

        $result = CreditReportServer::server()->experianCreditReport($order);
        dd($result);
    }
}
