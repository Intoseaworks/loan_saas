<?php

namespace Common\Console\Commands\Order;

use Common\Services\Order\OrderPayServer;
use Common\Services\Order\OrderServer;
use Common\Services\Pay\BasePayServer;
use Common\Utils\Email\EmailHelper;
use Illuminate\Console\Command;

class AutoDaikou extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:daikou';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动代扣收款';

    public function handle()
    {
        /** 代扣还款 */
        $this->daikou();
    }

    protected function daikou()
    {
        if (!BasePayServer::server()->hasDaikouOpen()) {
            return;
        }
        $orders = OrderServer::server()->getDaikouRepayOrders(['user.bankCards', 'lastRepaymentPlan']);
        $count = $orders->count();
        $success = $fail = 0;
        foreach ($orders as $order) {
            $orderPayServer = OrderPayServer::server();

            $result = $orderPayServer->daikou($order);

            if ($result) {
                $success++;
            }
        }
        if ($count > 0) {
            EmailHelper::send("成功{$success}/{$count}", '自动代扣回款');
            $this->line("成功{$success}/{$count}");
        }
    }
}
