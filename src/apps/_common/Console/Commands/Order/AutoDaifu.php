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
use Common\Utils\DingDing\DingHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;

class AutoDaifu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:daifu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动代付放款';

    public function handle()
    {
        /** 代付放款 */
        $this->daifu();
    }

    protected function daifu()
    {
        /** Env全局代付开关是否开启 */
        if (!BasePayServer::server()->envAutoRemit()) {
            return;
        }
        $orders = OrderServer::server()->waitPayOrders(['user.bankCards']);

        $count = $orders->count();
        
        echo "Daifu To Start Total:{$count}" . PHP_EOL;
        $success = $fail = $excess = 0;
        foreach ($orders->get() as $order) {
            MerchantHelper::setAppId($order->app_id, $order->merchant_id);

            /** 商户代付开关是否开启 */
            /** tracy 20210804 取消判断前移到自动签约控制
            
            if (!BasePayServer::server()->hasDaifuOpen()) {
                $count--;
                continue;
            }
*/
            $orderPayServer = OrderPayServer::server();

            $result = $orderPayServer->daifu($order);

            if ($result->isSuccess()) {
                $success++;
            }

            if ($result->eqCode(OrderPayServer::OUTPUT_DAIFU_EXCESS)) {
                $excess++;
            }

            // 发起支付调用之后(不管成功失败),sleep
            if (
                $result->isSuccess() ||
                $result->eqCode(OrderPayServer::OUTPUT_DAIFU_FAIL)
            ) {
                sleep(2);
            }
        }
        if ($count > 0 && $count != $excess) {
            DingHelper::notice("成功:{$success}/{$count},超额:{$excess}", '自动代付放款-' . app()->environment(), DingHelper::AT_SOLIANG);
            $this->line("成功{$success}/{$count}");
        }
        $this->line('Over');
    }
}
