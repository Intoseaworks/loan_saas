<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/24
 * Time: 15:36
 */

namespace Tests\Api\Repay;

use Common\Models\Order\Order;
use Common\Models\Order\RepaymentPlan;
use Common\Models\Trade\TradeLog;
use Common\Services\Pay\BasePayServer;
use Tests\Api\TestBase;

class RepayTest extends TestBase
{
    /**
     * 还款方式列表
     * @throws \Common\Exceptions\ApiException
     */
    public function testRepayMode()
    {
        $params = [
            'token' => $this->getToken(193),
        ];
        $this->get('app/repay/mode', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData(true, true);
    }

    /**
     * 一键代扣
     * @throws \Common\Exceptions\ApiException
     */
    public function testDaikou()
    {
        $params = [
            'token' => $this->getToken(2),
            'order_id' => '2',
        ];
        $this->get('app/repay/daikou', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    /**
     * 查询交易状态
     * @throws \Common\Exceptions\ApiException
     */
    public function testQueryTrade()
    {
        $params = [
            'token' => 705,
        ];
        $this->get('app/repay/query-trade', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    public function testQueryOrder()
    {
        $no = '5C85DD473E1CC20190311';
        $result = BasePayServer::server()->executeQueryOrder($no);
        dd($result->getData());
    }

    public function testRepay()
    {
        $params = [
            'token' => 253,
            'channel' => TradeLog::TRADE_PLATFORM_RAZORPAY, // TRADE_PLATFORM_MOBIKWIK  TRADE_PLATFORM_RAZORPAY  TRADE_PLATFORM_MPURSE
            'orderId' => 541,
//            'payerAccount' => 'Bodhi@icici',
            'repayAmount' => '2000',
        ];
        $this->post('app/repay/repay', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    public function testAppRepay()
    {
        $params = [
            'token' => 656,
            'channel' => TradeLog::TRADE_PLATFORM_MPURSE, // TRADE_PLATFORM_MPURSE  TRADE_PLATFORM_MOBIKWIK
            'orderId' => 826,
        ];
        $this->post('app/repay/app-repay', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    public function testCallbackHtmlPay()
    {
        $params = [
            //'token' => $this->getToken(9),
        ];
        $this->post('app/callback/html-pay', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    public function testCallbackHtmlRedirect()
    {
        $params = [
            //'token' => $this->getToken(9),
            'transaction_no' => '5D7F0A5FB6F4220190916',
            'result' => 'SUCCESS',
        ];
        $this->post('app/callback/html-redirect', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    public function testT()
    {
//        $order = Order::query()->find(750);
//        $order = Order::query()->find(1021);
        $order = Order::query()->find(541);
        $repaymentPlans = RepaymentPlan::getNeedRepayRepaymentPlans($order);
        $arr = [];
        foreach ($repaymentPlans as $item) {
            $arr[] = $order->repayAmount($item);
        }
        $amount = $order->repayAmount();

        dd($amount, ...$arr);
    }
}
