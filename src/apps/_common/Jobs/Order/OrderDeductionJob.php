<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/31
 * Time: 15:42
 */

namespace Common\Jobs\Order;

use Admin\Services\Repayment\ManualRepaymentServer;
use Common\Jobs\Job;
use Admin\Models\Order\Order;
use Common\Models\Trade\TradeLog;
use Common\Services\Repay\RepayServer;
use Common\Utils\MerchantHelper;
use Exception;

class OrderDeductionJob extends Job {

    const DEDUCTION_AMOUNT = 20;

    public $queue = 'order_deduction';
    public $tries = 3;
    public $orderId;
    public $merchantId;

    public function __construct($orderId, $merchantId = 1) {
        $this->orderId = $orderId;
        $this->merchantId = $merchantId;
    }

    public function handle() {
        MerchantHelper::helper()->setMerchantId($this->merchantId);
        $order = Order::model()->getOne($this->orderId);
        if ($order) {
            echo PHP_EOL . "开始处理订单({$order->id}):{$order->order_no}";
            try {
                $deduction = $order->repayAmount();
                if ($order && $deduction > 0 && $deduction <= self::DEDUCTION_AMOUNT) {
                    $repaymentPlan = $order->lastRepaymentPlan;
                    $tradeParams = [
                        'remark' => '自动减免',
                        'repay_name' => $order->user->fullname,
                        'repay_telephone' => $order->user->telephone,
                        'repay_account' => '',
                        'repay_time' => date('Y-m-d H:i:s'),
                        'repay_channel' => TradeLog::TRADE_PLATFORM_MANUAL_DEDUCTION,
                        'repay_amount' => $deduction
                    ];

                    $trade = ManualRepaymentServer::server()->addRepayTradeLog($order, TradeLog::TRADE_PLATFORM_DEDUCTION, $deduction, $tradeParams);

                    RepayServer::server($repaymentPlan, $trade)->completeRepay();
                    echo " - 处理完毕";
                } else {
                    echo " - 不满足条件";
                }
            } catch (Exception $ex) {
                echo $ex->getMessage();
            }
        } else {
            echo $this->orderId . "不存在";
        }
    }

}
