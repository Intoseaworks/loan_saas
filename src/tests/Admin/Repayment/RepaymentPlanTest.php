<?php

namespace Tests\Admin\Repayment;

use Tests\Admin\TestBase;

class RepaymentPlanTest extends TestBase
{
    /**
     * 还款计划列表
     */
    public function testList()
    {
        $params = [
            'sort' => '-overdue_days'
        ];
        $this->get('/api/repayment-plan/list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();

        $params['export'] = 1;
        $this->json('GET', '/api/repayment-plan/list', $params);
        $this->assertResponseOk();
    }

    /**
     * 已还款列表
     */
    public function testPaidList()
    {
        $params = [
            'sort' => 'created_at',
        ];
        $this->get('/api/repayment-plan/paid-list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();

        $params['export'] = 1;
        $this->json('GET', '/api/repayment-plan/paid-list', $params);
        $this->assertResponseOk();
    }

    /**
     * 已逾期列表
     */
    public function testOverdueList()
    {
        $params = [
            'sort' => 'overdue_days',
        ];
        $this->get('/api/repayment-plan/overdue-list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();

        $params['export'] = 1;
        $this->json('GET', '/api/repayment-plan/overdue-list', $params);
        $this->assertResponseOk();
    }

    /**
     * 已坏账列表
     */
    public function testBadList()
    {
        $params = [
        ];
        $this->get('/api/repayment-plan/bad-list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();

        $params['export'] = 1;
        $this->json('GET', '/api/repayment-plan/bad-list', $params);
        $this->assertResponseOk();
    }

    /**
     * 还款计划详情
     */
    public function testView()
    {
        $params = [
            'id' => 1
        ];
        $this->get('/api/repayment-plan/view', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();
    }

    /**
     * 已还款详情
     */
    public function testPaidView()
    {
        $params = [
            'id' => 1
        ];
        $this->get('/api/repayment-plan/paid-view', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();
    }

    /**
     * 已逾期详情
     */
    public function testOverdueView()
    {
        $params = [
            'id' => 1
        ];
        $this->get('/api/repayment-plan/overdue-view', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();
    }

    /**
     * 已坏账详情
     */
    public function testBadView()
    {
        $params = [
            'id' => 1
        ];
        $this->get('/api/repayment-plan/bad-view', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();
    }
}
