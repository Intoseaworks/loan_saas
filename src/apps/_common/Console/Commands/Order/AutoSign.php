<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Order;

use Admin\Models\Order\Order;
use Admin\Services\TradeManage\RemitServer;
use Common\Models\Trade\TradeLog;
use Common\Services\Pay\BasePayServer;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;
use Common\Models\BankCard\BankCardPeso;

class AutoSign extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:auto-sign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动签约';

    public function handle() {
        $this->sign();
    }

    protected function sign() {
        /** Env全局代付开关是否开启 */
        if (!BasePayServer::server()->envAutoRemit()) {
            return;
        }
        $params['status'] = Order::WAIT_CONFIRM_PAY_STATUS;
        $query = Order::model()->searchRemit($params, ['manualApprove']);
        $list = $query->get();
        $week = date("w");
        $time = intval(date("Gi"));
        foreach ($list as $order) {
            echo "orderId:{$order->id}<====>merchantId:".$order->merchant_id.PHP_EOL;
            MerchantHelper::helper()->setMerchantId($order->merchant_id);
            if (!BasePayServer::server()->hasDaifuOpen()) {
                echo '没有开启自动放款';
                continue;
            }
            if (in_array($order->merchant_id, []) //2, 3
                    && in_array($week, [1,2,3,4,5]) 
                    && $time<=1230 && $time>=930 
                    && BankCardPeso::PAYMENT_TYPE_CASH != $order->user->bankCard->payment_type) {
                echo 'to dgPay'.PHP_EOL;
                RemitServer::server()->manualConfirmLoan($order, TradeLog::TRADE_PLATFORM_DRAGONPAY);
            }else{
                echo 'to skypay'.PHP_EOL;
                RemitServer::server()->manualConfirmLoan($order, TradeLog::TRADE_PLATFORM_SKYPAY);
            }
        }
    }

}
