<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/24
 * Time: 15:36
 */

namespace Tests\Api\SaasMaster;

use Tests\Api\TestBase;

class PullConsumeLogTest extends TestBase
{
    /**
     * 拉取订单计费数据
     */
    public function testPullOrderData()
    {
        $params = [
            'time_start' => '2019-09-12 00:00:00',
            'time_end' => '2019-09-12 23:59:59',
        ];

        $this->sign($params);

        $this->post('app/saas-master/consume/pull-order', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    /**
     * 拉取机审计费数据
     */
    public function testPullSystemApproveData()
    {
        $params = [
            'time_start' => '2019-12-08 00:00:00',
            'time_end' => '2019-12-12 23:59:59',
        ];

        $this->sign($params);
        $this->post('app/saas-master/consume/pull-system-approve', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }
}
