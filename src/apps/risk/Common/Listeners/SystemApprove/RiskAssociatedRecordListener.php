<?php

namespace Risk\Common\Listeners\SystemApprove;

use Common\Utils\MerchantHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Risk\Common\Events\SystemApprove\SystemApproveFinishEvent;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Models\Business\User\User;
use Risk\Common\Models\Task\Task;
use Risk\Common\Services\Risk\RiskAssociatedRecordServer;

class RiskAssociatedRecordListener implements ShouldQueue
{
    public $queue = 'risk-default';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        MerchantHelper::clearMerchantId();
    }

    /**
     * @param SystemApproveFinishEvent $event
     * @return bool
     * @throws \Exception
     */
    public function handle(SystemApproveFinishEvent $event)
    {
        $appId = $event->getAppId();
        $taskId = $event->getTaskId();

        MerchantHelper::setMerchantId($appId);
        $task = Task::getById($taskId);

        if (!$task) {
            throw new \Exception("任务不存在。task_id:{$task->id}");
        }

        MerchantHelper::setMerchantId($task->app_id);

        $order = (new Order())->getOne($task->order_no);
        $user = (new User)->getOne($task->user_id);

        if (!$order || !$user) {
            throw new \Exception("任务对应order或user不存在.\ntask_id:{$task->id}\nuser_id:{$task->user_id}\norder_id:{$task->order_no}");
        }

        $server = RiskAssociatedRecordServer::server($user, $order);
        $server->addRiskAssociatedRecord();

        $task->updateAccountId($server->getAccountId());

        return true;
    }
}
