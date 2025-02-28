<?php

namespace Risk\Common\Console\SystemApprove;

use Common\Models\Order\Order;
use Illuminate\Console\Command;
use Risk\Common\Services\SystemApprove\ComputeRiskDataSandboxServer;

class ComputeRiskDataSandbox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'risk:compute-ridkdata-sandbox {--orderIds=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '风控：计算分控数据-沙盒模式 --orderIds=1,2,3';

    public function handle()
    {
        $this->computeRiskDataSandbox();
    }

    protected function computeRiskDataSandbox()
    {
        if (!$orderIds = $this->option('orderIds')) {
            $this->line('orderIds缺失');
            exit;
        }
        if (!$orderIdsArr = explode(',', $orderIds)) {
//            $this->line('orderIds格式不正确，举例orderIds=1,2,3');
//            exit;
        }
//        $orderModels = Order::where('status', Order::STATUS_WAIT_SYSTEM_APPROVE)->whereIn('id', $orderIdsArr)->get();
        $orderModels = Order::where('signed_time','>=', '2021-10-01')->get();
        foreach ($orderModels as $order) {
            $mod = $order->user_id%10;
            if ( $mod ==2 || $mod==3 ){
                ComputeRiskDataSandboxServer::server()->computeOrder($order);
            }
        }
    }
}
