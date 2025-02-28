<?php

namespace Tests\Api\Common;

use Tests\Api\TestBase;

class CallbackTest extends TestBase
{
    public function testDaikouNotice()
    {
        $params = [
            'transactionNo' => '5CB6A40A0E8D020190417',
            'tradeTime' => '1555312205',
            'requestNo' => 'requestNo1111111',
            'tradeNo' => 'tradeNo111111',
            'status' => 'SUCCESS',
        ];
        $this->sign($params);
        $this->post('/app/callback/daikou-notice', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }
}
