<?php

namespace Tests\Api\Order;

use Tests\Api\TestBase;

class RenewalTest extends TestBase
{
    /**
     * 获取订单续期信息
     */
    public function testGetRenewalInfo()
    {
        $params = [
            'token' => $this->getToken(93),
            'order_id' => 124,
        ];
        $this->get('/app/renewal/get-renewal-info', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    /**
     * 确认续期
     */
    public function testConfirmRenewal()
    {
        $params = [
            'token' => $this->getToken(93),
            'order_id' => 124,
        ];
        $this->post('/app/renewal/confirm-renewal', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

}
