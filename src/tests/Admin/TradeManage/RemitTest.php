<?php

namespace Tests\Admin\TradeManage;

use Common\Models\Order\Order;
use Common\Models\Trade\TradeLog;
use Common\Services\Pay\BasePayServer;
use Common\Utils\MerchantHelper;
use Tests\Admin\TestBase;

class RemitTest extends TestBase
{
    /**
     * 人工出款列表
     */
    public function testManualRemitList()
    {
        $this->json('GET', 'api/trade-manage/manual-remit-list', ['payment_type' => 'bank'])->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    /**
     * 人工出款详情
     */
    public function testManualRemitDetail($orderId = 0)
    {
        $query = Order::where('status', Order::STATUS_SIGN);
        if ($orderId != 0) {
            $query->where('id', $orderId);
        }
        $order = $query->first();

        $this->assertNotNull($order);

        $params = [
            'id' => $order->id,
        ];
        $this->json('GET', 'api/trade-manage/manual-remit-detail', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

    /**
     * 人工出款提交结果:失败
     */
    public function testManualRemitSubmit($orderId = 0)
    {
        $query = Order::where('status', Order::STATUS_SIGN);
        if ($orderId != 0) {
            $query->where('id', $orderId);
        }
        $order = $query->first();
//        $adminTradeAccount = AdminTradeAccount::where([
//            'type' => AdminTradeAccount::TYPE_OUT,
//            'status' => AdminTradeAccount::STATUS_ACTIVE,
//            'payment_method' => AdminTradeAccount::PAYMENT_METHOD_BANK,
//        ])->first();
//
//        $this->assertNotNull($order);
//        $this->assertNotNull($adminTradeAccount);

        $params = [
            'order_id' => $order->id,
            //'trade_account_id' => $adminTradeAccount->id,
            'trade_account' => 'test',
//            'trade_result' => TradeLog::TRADE_RESULT_FAILED,
            'trade_result' => TradeLog::TRADE_RESULT_SUCCESS,
            'remark' => '测试成功',
        ];
        $this->json('POST', 'api/trade-manage/manual-remit-submit', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    /**
     * 出款失败列表
     */
    public function testFailList()
    {
        $params = [
//            'trade_result_time' => [
//                '2019-01-29 16:33:04',
//                '2019-01-29 16:35:04',
//            ],
        ];
        $this->json('GET', 'api/trade-manage/fail-list', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    /**
     * 批量取消
     */
    public function testCancelBatch()
    {
        $params = $this->decode('{"ids":[1,50]}');
        $this->json('POST', 'api/trade-manage/cancel-batch', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

    public function testT()
    {
        MerchantHelper::setMerchantId(2);
        $tradeLog = TradeLog::query()->where('trade_platform', 'paytm')->get();

        $orderPayServer = BasePayServer::server();

        $error = [];
        foreach ($tradeLog as $item) {
            $result = $orderPayServer->executeQueryOrder($item->transaction_no);

            if (!$result->isSuccess()) {
                $error[] = [$item->transaction_no];
            }
            $data = $result->getData();

            if ($item->trade_result == TradeLog::TRADE_RESULT_SUCCESS && $data['status'] != 'SUCCESS') {
                $error[] = [$item->transaction_no];
            } elseif ($item->trade_result == TradeLog::TRADE_RESULT_FAILED && $data['status'] != 'FAILED') {
                $error[] = [$item->transaction_no];
            } elseif ($item->trade_result == TradeLog::TRADE_RESULT_NULL) {
                continue;
            }
        }
        dd($error);
    }
}
