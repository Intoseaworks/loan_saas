<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/2/15
 * Time: 10:19
 */

namespace Tests\Api\Common;

use Tests\Api\TestBase;

class ChannelTest extends TestBase
{
    public function testChannelRecord()
    {
        $json = '{"type":"download_uv","id":1}';
        $params = json_decode($json, true);

        $this->json('POST', 'app/channel/record', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }
}
