<?php

namespace Tests\Api\Partner;

use Tests\Api\TestBase;

class PartnerAccountTest extends TestBase
{
    /**
     * 商户详情
     */
    public function testPartnerDetail()
    {
        $params = [
        ];
        $this->json('get', 'api/partner-account/partner/detail', $params)->seeJson([
            'code' => 18000,
        ])->getData();
    }

    /**
     * 商户消费记录统计列表
     */
    public function testConsumeList()
    {
        $params = [
        ];
        $this->json('get', 'api/partner-account/consume/list', $params)->seeJson([
            'code' => 18000,
        ])->getData();
    }

    /**
     * 商户消费记录明细列表
     */
    public function testConsumeLogList()
    {
        $params = [
            'date' => '2018-10-02',
        ];
        $this->json('get', 'api/partner-account/consume-log/list', $params)->seeJson([
            'code' => 18000,
        ])->getData();
    }

    /**
     * 商户账户每日统计列表
     */
    public function testAccountStatisticsList()
    {
        $params = [
            'partner_id' => 20,
        ];
        $this->json('get', 'api/partner-account/account-statistics/list', $params)->seeJson([
            'code' => 18000,
        ])->getData();
    }
}
