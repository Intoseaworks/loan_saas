<?php

namespace Risk\Api\Services\Task;

use Common\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Models\Business\User\User;
use Risk\Common\Models\Task\Task;
use Risk\Common\Models\Task\TaskData;

class TaskServer extends BaseService
{
    public function startTask($userId, $orderId, $noticeUrl = null)
    {
        $task = DB::connection((new Task())->getConnectionName())->transaction(function () use ($userId, $orderId, $noticeUrl) {
            $task = Task::add($userId, $orderId, $noticeUrl);
            TaskData::initTaskData($task->id);
            return $task;
        });

        $required = $task->getTaskDataLacking();

        return $this->outputSuccess('ok', ['taskNo' => $task->task_no, 'required' => $required]);
    }

    public function execTask($taskNo)
    {
        $task = Task::getByTaskNo($taskNo);

        if (!$task) {
            return $this->outputError('任务不存在或状态不正确');
        }

        if ($task->status != Task::STATUS_CREATE) {
            return $this->outputSuccess('ok', ['status' => $task->status]);
        }

        if (!$task->isFinishDataSend()) {
            $required = $task->getTaskDataLacking();

            return $this->outputSuccess('required data is incomplete', ['status' => $task->status, 'required' => $required]);
        }

        if (
            !(new Order)->getOne($task->order_no) ||
            !(new User)->getOne($task->user_id)
        ) {
            return $this->outputSuccess('required data is incomplete. there is no corresponding order data or user data', ['status' => $task->status, 'required' => [
                TaskData::TYPE_USER,
                TaskData::TYPE_ORDER,
            ]]);
        }

        $task->toWaiting();

        return $this->outputSuccess('ok', ['status' => $task->refresh()->status]);
    }
}
