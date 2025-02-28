<?php

namespace Risk\Common\Services\TaskData;

use Common\Services\BaseService;
use Risk\Common\Models\Task\Task;
use Risk\Common\Models\Task\TaskData;

class TaskDataServer extends BaseService
{
    public function sendDataFinish(Task $task, $finishType)
    {
        return $task->taskData()->whereIn('type', (array)$finishType)->update([
            'status' => TaskData::STATUS_FINISH,
        ]);
    }
}
