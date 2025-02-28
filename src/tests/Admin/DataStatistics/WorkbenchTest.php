<?php

namespace Tests\Admin\DataStatistics;

use Tests\Admin\TestBase;

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/10
 * Time: 21:25
 */
class WorkbenchTest extends TestBase
{
    /**
     * 控制台首页
     */
    public function testIndex()
    {
        $params = [

        ];
        $this->json('GET', '/api/data_statistics/workbench/index', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();
    }

    /**
     * 控制台折线图
     */
    public function testLine()
    {
        $params = [
            'type' => 'day_remit_sum',
            'date' => ['2019-03-04', '2019-03-06'],
        ];
        $this->json('GET', '/api/data_statistics/workbench/line', $params)
            ->getData();
        /*$this->json('GET', '/api/data_statistics/workbench/line', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);*/
    }

}