<?php

namespace Tests\Admin\CollectionStatistics;

use Tests\Admin\TestBase;

class CollectionStatisticsTest extends TestBase
{
    /**
     * 催收订单统计列表
     */
    public function testList()
    {
//        $ss = StatisticsCollection::find(1);
//        dd(get_class($ss->order()->getRelated()->lastRepaymentPlan()->getRelated()));

        $params = [
//            'date' => [
//                '2019-2-11',
//                '2019-2-12'
//            ],
            'sort' => '-date',
        ];
        $this->json('GET', '/api/collection-statistics/list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();

        $params['export'] = 1;
        $this->json('GET', '/api/collection-statistics/list', $params);
        $this->assertResponseOk();
    }

    /**
     * 催回率统计列表
     */
    public function testRateList()
    {
        $params = [
//            'date' => [
//                '2019-2-11',
//                '2019-2-12'
//            ],
            'sort' => '-date',
        ];
        $this->json('GET', '/api/collection-statistics/rate-list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();

        $params['export'] = 1;
        $this->json('GET', '/api/collection-statistics/rate-list', $params);
        $this->assertResponseOk();
    }

    /**
     * 催收员每日统计列表
     */
    public function testStaffList()
    {
        $params = [
//            'date' => [
//                '2019-2-11',
//                '2019-2-12'
//            ],
            'sort' => 'date',
        ];
        $this->json('GET', '/api/collection-statistics/staff-list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();

        $params['export'] = 1;
        $this->json('GET', '/api/collection-statistics/staff-list', $params);
        $this->assertResponseOk();
    }

    /**
     * 催收员每日统计列表
     */
    public function testEfficiencyList()
    {
        $params = [
            'date' => [
                '2019-2-11',
                '2020-2-12'
            ]
        ];
        $this->json('GET', '/api/collection-statistics/efficiency-list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();
        $params['export'] = 1;
        $this->json('GET', '/api/collection-statistics/efficiency-list', $params);
        $this->assertResponseOk();
    }
}
