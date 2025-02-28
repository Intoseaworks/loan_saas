<?php

namespace Tests\Admin\Collection;

use Tests\Admin\TestBase;

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/10
 * Time: 21:25
 */
class CollectionDeductionTest extends TestBase
{
    /**
     * 添加减免
     */
    public function testCreate($params = [])
    {
        if (!$params) {
            $params = [
                'collection_id' => 16,
                'deduction' => 100,
                'deduction_time' => ['2019-01-01', '2019-03-01'],
            ];
        }
        //$this->json('POST', '/api/collection_deduction/create', $params)->getData();
        $this->json('POST', '/api/collection_deduction/create', $params)->seeJson(['code' => self::SUCCESS_CODE]);
    }

    /**
     * 减免信息
     */
    public function testInfo()
    {
        $params = [
            'collection_id' => 8,
        ];
        $this->json('GET', '/api/collection_deduction/info', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

}