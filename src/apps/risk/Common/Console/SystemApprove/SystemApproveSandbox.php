<?php

namespace Risk\Common\Console\SystemApprove;

use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Services\SystemApprove\SystemApproveSandboxServer;

class SystemApproveSandbox extends SystemApprove
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'risk:system-approve-sandbox {--orderIds=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '审批：机审-沙盒模式 --orderIds=1,2,3';

    public function handle()
    {
        $this->systemApprove();
    }

    protected function systemApprove()
    {
        if (!$orderIds = $this->option('orderIds')) {
            $this->line('orderIds缺失');
            exit;
        }
        if (!$orderIdsArr = explode(',', $orderIds)) {
            $this->line('orderIds格式不正确，举例orderIds=1,2,3');
            exit;
        }
        $orderModels = Order::query()->whereIn('id', $orderIdsArr)->get();

        foreach ($orderModels as $order) {
            SystemApproveSandboxServer::server()->approveOrder($order);
        }
    }
}
