<?php

namespace Risk\Common\Services\Task;

use Common\Services\BaseService;
use Common\Utils\DingDing\DingHelper;
use JMD\Utils\SignHelper;
use Risk\Common\Jobs\TaskNoticeJob;
use Risk\Common\Models\Task\Task;
use Risk\Common\Models\Task\TaskNotice;

class TaskNoticeServer extends BaseService
{
    use RiskNoticeCurl;

    public function noticeTask(Task $task)
    {
        $task->refresh();
        $taskNotice = new TaskNotice();
        $noticeNum = $taskNotice->getCountByTask($task->id);
        //通知次数限制
        if ($noticeNum >= TaskNotice::NOTICE_NUM_LIMIT) {
            return false;
        }
        //判断是否已通知成功，成功则不再重复通知
        if ($taskNotice->hasSuccessNotice($task->id)) {
            return false;
        }

        return $this->noticeOrRetry($task, $noticeNum);
    }

    /**
     * 先同步执行一次通知。失败则重新入列
     * @param $task
     * @param int $noticeNum
     * @return bool
     */
    public function noticeOrRetry(Task $task, $noticeNum = 0)
    {
        $noticeResult = $this->noticeExec($task);

        //响应失败 && 通知次数少于六次 再次投放到通知队列
        if (!$noticeResult) {
            $this->addTaskNoticeQueue($task, $noticeNum * 60);
            return false;
        }

        return true;
    }

    /**
     * 通知执行
     * @param $task
     * @return bool
     */
    public function noticeExec(Task $task)
    {
        try {
            $task = $task->refresh();

            if (!in_array($task->status, [Task::STATUS_FINISH, Task::STATUS_EXCEPTION,])) {
                DingHelper::notice(var_export([
                    'task' => json_encode($task),
                    'error' => '机审未完结即推推送队列'
                ], true), '【事件抛错】-机审通知1');
                return true;
            }

            $noticeUrl = $task->notice_url ?? null;

            if (!$noticeUrl) {
                return (new TaskNotice())->add([
                    'task_id' => $task->id,
                    'status' => TaskNotice::STATUS_NO_NOTICE,
                    'remark' => '通知地址不存在',
                ]);
            }

            //通知模板
            $noticeInfo = [
                'app_key' => $task->app->app_key ?? '',
                'task_no' => $task->task_no,
                'order_id' => $task->order_no,
                'status' => $task->status,
                'result' => $task->result,
                'hit_rule_code' => $task->hit_rule_code,
                'task_desc' => $task->task_desc,
            ];

            $appSecretKey = $task->app->app_secret_key;
            $noticeInfo['sign'] = SignHelper::sign($noticeInfo, $appSecretKey);

            $response = self::postToJsonCurl($noticeUrl, $noticeInfo);
            //记录通知信息
            $notice = [
                'task_id' => $task->id,
                'notice_url' => $noticeUrl,
                'notice_info' => json_encode($noticeInfo),
                'response_info' => $response,
                'status' => $response == TaskNotice::RESPONSE_SUCCESS_FLAG ? TaskNotice::STATUS_RESPONSE_SUCCESS : TaskNotice::STATUS_RESPONSE_FAILED,
            ];
            (new TaskNotice())->add($notice);

            return $response == TaskNotice::RESPONSE_SUCCESS_FLAG;

        } catch (\Exception $exception) {
            DingHelper::notice(var_export([
                'task' => json_encode($task),
                'error' => $exception->getMessage()
            ], true), '【事件抛错】- 机审结果通知');

            return false;
        }
    }

    /**
     * 添加到通知队列
     * @param $task
     * @param int $delay
     * @return mixed
     */
    public function addTaskNoticeQueue($task, $delay = 1)
    {
        return dispatch((new TaskNoticeJob($task))->delay($delay));
    }
}
