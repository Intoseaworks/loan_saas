<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/1
 * Time: 11:01
 */

namespace Tests\Admin\Channel;

use Tests\Admin\TestBase;

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/10
 * Time: 21:25
 */
class ChannelStatisticsTest extends TestBase
{

    /**
     * 列表
     */
    public function testIndex()
    {
        $params = [

        ];
        $this->json('GET', '/api/channel-statistics/index', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

    /**
     * 详情
     */
    public function testView()
    {
        $params = [

        ];
        $this->json('GET', '/api/channel-statistics/view', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

}
