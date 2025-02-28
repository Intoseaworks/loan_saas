<?php

namespace Tests\Admin\Partner;

use Tests\Admin\TestBase;

class PartnerRechargeTest extends TestBase
{
    /**
     * 充值列表
     */
    public function testRechargeList()
    {
        $params = [
            'sort' => 'reduction_fee',
        ];
        $this->get('/api/partner-recharge/list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();
    }

    /**
     * 充值申请
     */
    public function testRechargeApply()
    {
        $params = [
            'recharge_amount' => mt_rand(1000, 10000),
            'recharge_voucher' => [1,2],
        ];
        $this->get('/api/partner-recharge/apply', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();
    }
}
