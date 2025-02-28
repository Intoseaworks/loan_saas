<?php

namespace Common\Jobs\Crm;

use Admin\Services\Crm\MarketingServer;
use Common\Jobs\Job;
use Common\Models\Crm\CrmMarketingTask;
use Common\Models\Crm\MarketingPhoneAssign;

class MarketingStatisticsJob extends Job {

    public $queue = 'telemarketing2';
    public $tries = 3;
    public $taskId;

    public function __construct($taskId) {
        $this->taskId = $taskId;
    }

    public function handle() {
        $task = CrmMarketingTask::model()->getOne($this->taskId);
        if ($task) {
            echo "ST[{$task->id}]" . $task->task_name . " start". PHP_EOL;
            $task->phone_assign_total = MarketingPhoneAssign::model()->newQuery()->where("task_id", $task->id)->count();
            $task->phone_total = count(MarketingServer::server()->getTaskCustomer($task, false))+$task->phone_assign_total-$task->other_assigned_total;
            $task->save();
        } else {
            echo "ST[{$this->taskId}] not found" . PHP_EOL;
        }
    }

}
