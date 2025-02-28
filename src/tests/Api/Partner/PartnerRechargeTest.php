<?php

namespace Tests\Api\Partner;

use Tests\Api\TestBase;

class PartnerRechargeTest extends TestBase
{
    public function testPartnerRechargeList()
    {
        $params = [
        ];
        $this->json('get', 'api/partner-recharge/list', $params)->seeJson([
            'code' => 18000,
        ])->getData();
    }
}
