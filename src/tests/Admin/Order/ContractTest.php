<?php

use Tests\Admin\TestBase;

class ContractTest extends TestBase
{
    /**
     * 订单放款列表
     */
    public function testContractList()
    {
        $params = [
        ];
        $this->json('GET', '/api/contract/index', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();
    }

    /**
     * 放款订单详情
     */
    public function testContractView()
    {
        $params = [
            'id' => 130,
        ];
        $this->json('GET', '/api/contract/view', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();
    }
}
