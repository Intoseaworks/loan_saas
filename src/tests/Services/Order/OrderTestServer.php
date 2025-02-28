<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Tests\Services\Order;

use Api\Models\User\User;
use Api\Services\Order\OrderCheckServer;
use Api\Services\Order\OrderServer;
use Tests\Api\Order\OrderTest;
use Tests\Services\BaseService;
use Tests\TestCase;

class OrderTestServer extends BaseService
{
    /**
     * 借款全流程
     *
     * @param $userId
     */
    public function loan($userId)
    {
        $orderTest = new OrderTest();
        $orderTest->setUp();
        # 清空历史订单
        OrderCheckServer::server()->deleteHasExists($userId);
        $order = $orderTest->testCreate($userId);
        $orderId = $order->id;
        TestCase::$orderId = $orderId;
        //TODO 机审
        //暂时跳过机审
        OrderServer::server()->systemApproving(TestCase::$orderId);
        //OrderServer::server()->systemToManual(TestCase::$orderId);

        //TODO 审批用例


        //签约需在第三方页面，直接签约
        $user = User::model()->getOne($userId);
        //订单金额，天数更新预留
        $updateOrderData = [];
        OrderServer::server()->sign($user, $updateOrderData);
        return $order;
    }
}
