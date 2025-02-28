<?php

namespace Risk\Common\Jobs;

use Common\Utils\DingDing\DingHelper;
use Risk\Common\Models\Task\Task;
use Risk\Common\Services\Task\TaskNoticeServer;

class TaskNoticeJob extends Job
{
    public $queue = 'risk-default';

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 3;

    /**
     * @var Task
     */
    public $task;

    public function __construct($task)
    {
        $this->task = $task;
    }

    public function handle()
    {
        try {
            TaskNoticeServer::server()->noticeTask($this->task);
        } catch (\Exception $exception) {
            DingHelper::notice(json_encode($this->task) . 'ERROR:' . $exception->getMessage(), '机审结果通知任务队列抛错');
        }
    }
}
