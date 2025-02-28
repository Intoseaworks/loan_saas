<?php

namespace Tests\Admin\Partner;

use Tests\Admin\TestBase;

class PartnerBalanceTest extends TestBase
{
    /**
     * 余额查询
     */
    public function testBalanceQuery()
    {
        $this->get('/api/partner-balance/query')
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();
    }
}
