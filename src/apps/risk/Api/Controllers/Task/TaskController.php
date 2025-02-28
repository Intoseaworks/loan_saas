<?php

namespace Risk\Api\Controllers\Task;

use Risk\Api\Rules\Task\TaskRule;
use Risk\Api\Services\Task\TaskServer;
use Risk\Common\Controller\RiskBaseController;

class TaskController extends RiskBaseController
{
    /**
     * 创建任务
     * @param TaskRule $rule
     * @return array
     */
    public function startTask(TaskRule $rule)
    {
        $params = $this->request->all();
        if (!$rule->validate($rule::SCENARIO_START_TASK, $params)) {
            return $this->resultFail($rule->getError());
        }
        $userId = $this->request->get('user_id');
        $orderId = $this->request->get('order_id');
        $noticeUrl = $this->request->get('notice_url');

        $server = TaskServer::server()->startTask($userId, $orderId, $noticeUrl);

        if (!$server->isSuccess()) {
            return $this->resultFail($server->getMsg());
        }

        return $this->resultSuccess($server->getData(), $server->getMsg());
    }

    /**
     * 执行机审&验证数据上传
     * @param TaskRule $rule
     * @return array
     */
    public function execTask(TaskRule $rule)
    {
        $params = $this->request->all();
        if (!$rule->validate($rule::SCENARIO_EXEC_TASK, $params)) {
            return $this->resultFail($rule->getError());
        }

        $taskNo = $this->request->get('task_no');
        $server = TaskServer::server()->execTask($taskNo);

        if (!$server->isSuccess()) {
            return $this->resultFail($server->getMsg(), $server->getData());
        }

        return $this->resultSuccess($server->getData(), $server->getMsg());
    }
}
