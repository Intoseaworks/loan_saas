<?php

namespace Common\Console\Commands\Risk;

use Common\Console\Services\Risk\SystemApproveServer;
use Common\Models\Order\Order;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Lock\LockRedisHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;

class SystemApprove extends Command
{
    /**
     * The name and signature of the console command.
     * --once 在机审关闭的情况下，只执行一次
     * @var string
     */
    protected $signature = 'risk-business:system-approve:start {--once}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '风控-业务：机审创建';

    public function handle()
    {
        $this->systemApprove();
    }

    protected function systemApprove()
    {
        $once = $this->option('once');

        $orderModels = Order::getWaitSystemApprove();

        /** 检查待机审订单滞留 每小时告警一次 */
        $maxWaitOrder = 100;
        $count = $orderModels->count();
        if (LockRedisHelper::helper()->riskApproveMaxNotice(3600, 'start') && $count > $maxWaitOrder) {
            DingHelper::notice("机审订单滞留超过{$maxWaitOrder}单, 当前等待机审{$count}单", '机审单滞留,请检查!');
        }

        if (!SystemApproveServer::server()->envSystemApprove() && !$once) {
            return;
        }

        foreach ($orderModels as $order) {
            if (!SystemApproveServer::server()->envSystemApprove() && !$once) {
                return;
            }

            MerchantHelper::setMerchantId($order->merchant_id);

            SystemApproveServer::server()->approve($order);

            if ($once) {
                $once = false;
            }
        }
    }
}
