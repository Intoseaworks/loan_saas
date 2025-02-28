<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Order;


use Common\Services\Order\OrderPayServer;
use Common\Services\Order\OrderServer;
use Common\Services\Pay\BasePayServer;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;

class AutoDaifuOnce extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:daifu:once {orderId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动代付放款-指定订单(可用来测试渠道)';

    public function handle()
    {
        /** 代付放款 */
        $this->daifu();
    }

    protected function daifu()
    {
        $orderId = $this->argument('orderId');

        /** Env全局代付开关是否开启 */
        if (!BasePayServer::server()->envAutoRemit()) {
            $this->warn('放款总开关处于关闭');
            return;
        }
        $orders = OrderServer::server()->waitPayOrders(['user.bankCards']);
        $order = $orders->where('id', $orderId)->first();

        if (!$order) {
            $this->error('待放款订单不存在');
            return;
        }

        MerchantHelper::setAppId($order->app_id, $order->merchant_id);

        $orderPayServer = OrderPayServer::server();

        $result = $orderPayServer->daifu($order);

        if ($result->isSuccess()) {
            $this->info('放款发起成功');
            return;
        }

        if ($result->eqCode(OrderPayServer::OUTPUT_DAIFU_EXCESS)) {
            $this->warn('商户当日放款超额');
            return;
        }
    }
}
