<?php

namespace Tests\Admin\Repayment;

use Tests\Admin\TestBase;

class SystemRepayTest extends TestBase
{
    /**
     * 系统放款记录
     */
    public function testList()
    {
        $params = [
            'trade_result_time' => ['2019-03-10 00:00:00', '2022-01-01 00:00:00']
        ];
        $this->get('/api/trade-log/system-repay-list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();

        $params['export'] = 1;
        $this->json('GET', '/api/trade-log/system-repay-list', $params);
        $this->assertResponseOk();
    }
}
