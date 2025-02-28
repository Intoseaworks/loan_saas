<?php

namespace Tests\UnitTest\Risk;

use Common\Models\Order\Order;
use Common\Models\Risk\RiskBlacklist;
use Common\Services\Risk\RiskBlacklistServer;
use Common\Utils\MerchantHelper;
use Tests\Admin\TestBase;

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/10
 * Time: 21:25
 */
class RiskBlacklistTest extends TestBase
{
    /**
     * 添加
     */
    public function testSystemAddBlack()
    {
        MerchantHelper::setMerchantId(1);
        $order = Order::find(46621);
        RiskBlacklistServer::server()->systemAddBlack($order, RiskBlacklist::TYPE_OVERDUE, false);
        RiskBlacklistServer::server()->systemAddBlack($order, RiskBlacklist::TYPE_REFUSAL_CODE, true);
    }

    public function testHitRelate()
    {
        $order = Order::find(1629);
        dd(RiskBlacklistServer::server()->relateAddBlack($order));
    }

    public function testManualAddBlack()
    {
        $data = [
            'keyword' => RiskBlacklist::KEYWORD_BANKCARD,
            'value' => '5659561358164',
            'black_reason' => RiskBlacklist::TYPE_OFFLINE_IMPORT_SUSPECTED_FAKE,
            'merchant_id' => 1,
            'is_global' => RiskBlacklist::IS_GLOBAL_YES,
        ];
        dd(RiskBlacklistServer::server()->manualAddBlack(array_values($data)));
    }
}
