<?php

namespace Tests\Admin\OperateData;

use Tests\Admin\TestBase;


class BalanceOfPaymentsTest extends TestBase
{
    /**
     * 每日收支分析列表
     */
    public function testList()
    {
        $params = [

        ];
        $this->json('GET', '/api/balance-of-repayments/list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);

        $params['export'] = 1;
        $this->json('GET', '/api/balance-of-repayments/list', $params);
        $this->assertResponseOk();
    }

    /**
     * 每日收入分析列表
     */
    public function testIncomeList()
    {
        $params = [
            'date' => '2019-04-18',
        ];
        $this->json('GET', '/api/balance-of-repayments/income-list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);

        $params['export'] = 1;
        $this->json('GET', '/api/balance-of-repayments/income-list', $params);
        $this->assertResponseOk();
    }

    /**
     * 每日支出分析列表
     */
    public function testDisburseList()
    {
        $params = [
            'date' => '2019-02-26',

        ];
        $this->json('GET', '/api/balance-of-repayments/disburse-list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);

        $params['export'] = 1;
        $this->json('GET', '/api/balance-of-repayments/disburse-list', $params);
        $this->assertResponseOk();
    }

}
