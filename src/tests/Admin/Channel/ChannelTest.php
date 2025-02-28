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
class ChannelTest extends TestBase
{

    /**
     * 列表
     */
    public function testMonitorItem()
    {
        $params = [
            'channel_code' => '132'
        ];
        $this->json('GET', '/api/channel/monitor-item', $params)->seeJson(['code' => self::SUCCESS_CODE])
            ->getData();
    }
}
