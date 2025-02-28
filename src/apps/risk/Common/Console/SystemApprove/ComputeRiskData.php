<?php

namespace Risk\Common\Console\SystemApprove;

use Common\Models\Order\Order;
use Illuminate\Console\Command;
use Risk\Common\Models\Business\RiskData\RiskDataIndex;
use Risk\Common\Services\SystemApprove\ComputeRiskDataSandboxServer;

class ComputeRiskData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'risk:compute-riskdata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '风控：计算风控数据';

    public function handle()
    {
        $this->computeRiskData();
    }

    protected function computeRiskData()
    {
        $orderModels = Order::withoutGlobalScopes()->where('status', Order::STATUS_WAIT_SYSTEM_APPROVE)->whereQuality(Order::QUALITY_NEW)->get();
        // $orderModels = Order::withoutGlobalScopes()->where('signed_time','>=', '2021-10-01')->get();
        foreach ($orderModels as $order) {
            if (RiskDataIndex::whereOrderId($order->id)->whereIndexName('end_time')->first()){
                //已计算的跳过
                continue;
            }
            $mod = $order->user_id%10;
            if ( $mod ==2 || $mod==3 ){
                ComputeRiskDataSandboxServer::server()->computeOrder($order);
            }
        }
    }
}
