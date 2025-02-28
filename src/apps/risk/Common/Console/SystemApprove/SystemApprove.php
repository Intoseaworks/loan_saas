<?php

namespace Risk\Common\Console\SystemApprove;

use Common\Utils\DingDing\DingHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;
use Risk\Common\Events\SystemApprove\SystemApproveFinishEvent;
use Risk\Common\Helper\LockRedisHelper;
use Risk\Common\Models\Task\Task;
use Risk\Common\Services\SystemApprove\SystemApproveServer;

class SystemApprove extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'risk:system-approve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '审批：机审';

    public function handle()
    {
        $this->systemApprove();
    }

    protected function systemApprove()
    {
        $taskModels = Task::getWaitSystemApprove();

        /** 检查待机审订单滞留 每小时告警一次 */
        $maxWaitOrder = 100;
        $count = $taskModels->count();
        if (LockRedisHelper::helper()->riskApproveMaxNotice(3600) && $count > $maxWaitOrder) {
            DingHelper::notice("机审订单滞留超过{$maxWaitOrder}单, 当前等待机审{$count}单", '机审单滞留,请检查!');
        }

        if (!SystemApproveServer::server()->envSystemApprove()) {
            return;
        }

        foreach ($taskModels as $task) {
            MerchantHelper::setMerchantId($task->app_id);

            $res = SystemApproveServer::server()->approve($task);

            if ($res) {
                event(new SystemApproveFinishEvent($task->id, $task->app_id, $task->order_no));
            }
        }
    }
}
