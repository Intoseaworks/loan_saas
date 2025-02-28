<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/24
 * Time: 15:36
 */

namespace Tests\Api\Order;

use Api\Models\Order\Order;
use Tests\Api\TestBase;

class OrderTest extends TestBase
{
    public $order;

    public function testCalculate()
    {
        $params = [
            'order_id' => 1,
            'loan_days' => 30,
            'principal' => 1000,
            'token' => $this->getToken()
        ];
        $this->post('app/order/calculate', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
        $this->assertEquals('30.00', array_get($this->getData(), 'data.interest_fee'));
        $this->assertEquals('900.00', array_get($this->getData(), 'data.receivable_amount'));
    }

    /**
     * 创建订单
     */
    public function testCreate($userId = 0)
    {
//        if ($userId == 0) {
//            $userId = self::$userId;
//        }
//        OrderCheckServer::server()->deleteHasExists($userId);
//        # 需要无订单状态下执行
//        $params = [
//            'token' => $this->getToken($userId),
//            'client_id' => 'android',
//        ];
//        $this->post('app/order/create', $params)->seeJson([
//            'code' => self::ERROR_CODE,
//        ]);
//        $user = User::model()->getOne(1);
//        OrderServer::server()->userCancel($user->order->id);
        $params['token'] = 365;
        $params['position'] = json_encode([
            'address' => 'KHARANGAJHAR KRISHNA MANDIR,PO-TELCo WORKS PS-TELCO',
            'street' => 'ANDAMAN & NICOBAR ISLANDS',
            'longitude' => '22.532393000000',
            'latitude' => '113.948880000000',
        ], 256);
        $params['product_id'] = 1;
        $params['loan_reason'] = 'reason';
        $params['client_id'] = 'android';
        $params['intention_principal'] = 3000;
        $params['intention_loan_days'] = 15;
        $data = $this->post('app/order/create', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
        return $this->order = Order::model()->getOne($data['id']);
    }

    public function testSystemToManual()
    {

    }

    public function testAgreement()
    {
        $params = [
            'token' => $this->getToken(),
            'order_id' => $this->order->id,
            'type' => '借款协议',
        ];
        $this->get('app/order/agreement', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

    public function testReplenish()
    {
        $params = [
            'token' => $this->getToken(),
        ];
        $this->post('app/order/replenish', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

    /**
     * 订单签约
     */
    public function testSign()
    {
        # 需要有带签约状态订单执行
        $params = [
            'token' => $this->getToken($this->order->user_id),
        ];
        $this->post('app/order/sign', $params)->seeJson([
            'code' => self::ERROR_CODE,
        ]);
        $params['principal'] = 1500;
        $params['loan_days'] = 30;
        $this->post('app/order/sign', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

    /**
     * 取消订单
     */
    public function testCancel()
    {
        # 需要有订单状态下执行
        $params = [
            'token' => $this->getToken(),
        ];
        $this->post('app/order/cancel', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

    /**
     * 订单列表
     */
    public function testIndex()
    {
        $this->get('app/order/index?token=' . $this->getToken())->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

    /**
     * 订单详情
     */
    public function testDetail()
    {
        $params = [
            'token' => $this->getToken(1),
        ];
        $this->get('app/order/detail', $params)->seeJson([
            'code' => self::ERROR_CODE,
        ]);
        $params['order_id'] = 1;
        $this->get('app/order/detail', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

    public function testOrderReduction()
    {
        $params = [
            'token' => $this->getToken(274),
        ];
        $this->post('app/order/reduction', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

    public function testConfig()
    {
        $params = [
//            'token' => 269,
            'token' => 276,
        ];
        $this->get('app/order/config', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }
}
