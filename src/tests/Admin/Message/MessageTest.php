<?php

namespace Tests\Admin\Message;

use Api\Services\User\UserCheckServer;
use Common\Events\Order\OrderCreateEvent;
use Common\Events\Order\OrderRemitSuccessEvent;
use Common\Utils\MerchantHelper;
use Tests\Admin\TestBase;

class MessageTest extends TestBase
{
    public function testReadMessage()
    {
        $params = [
//            'id' => 93,
            'messageId' => 47,
        ];

        $this->json('POST', '/api/message/read', $params)->getData();
    }

    public function testT()
    {
        MerchantHelper::setMerchantId(1);

//        $res = OrderCheckServer::server()->reachMaxCreate();
//        $res = OrderCheckServer::server()->reachMaxLoanAmount(5000);
//        dd($res);

        $res = UserCheckServer::server()->reachMaxRegister();
        dd($res);
    }

    public function testEvent()
    {
        // 出款成功事件=>检测放款超额预警
        event(new OrderRemitSuccessEvent(1, 1));
        dd('s');
        // 订单创建事件=>检测订单创建超限预警
        event(new OrderCreateEvent(1, 1, 1));
    }
}