<?php

namespace Risk\Api\Controllers\Data;

use Common\Utils\DingDing\DingHelper;
use Illuminate\Support\Str;
use Risk\Api\Rules\Data\SendDataRule;
use Risk\Api\Services\Data\SendDataServer;
use Risk\Common\Controller\RiskBaseController;
use Risk\Common\Models\Task\Task;
use Risk\Common\Models\Task\TaskData;
use Risk\Common\Services\TaskData\TaskDataServer;

class SendDataController extends RiskBaseController
{
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAILED = 'FAILED';

    public function all(SendDataRule $rule)
    {
        $params = $this->request->all();
        if (!$rule->validate($rule::SCENARIO_SEND_DATA_ALL, $params)) {
            return $this->resultFail($rule->getError());
        }
        $userId = $this->request->get('user_id');
        $taskNo = $this->request->get('task_no');

        if ($taskNo) {
            $task = Task::getByTaskNo($taskNo);

            if (!$task || $task->user_id != $userId) {
                return $this->resultFail('任务编号错误');
            }
        }

        $server = SendDataServer::server();
        $result = [];
        foreach (TaskData::OUTER_TYPE as $type) {
            try {
                $method = "save" . Str::studly(Str::lower($type));

                if ($this->request->has($type) && is_callable([$server, $method])) {
                    $data = $this->request->get($type);
                    if (!is_array($data)) {
                        return $this->resultFail(array_get(TaskData::TYPE, $type, '') . 'field is not formatted correctly');
                    }
                    if ($data) {
                        $res = $server->$method($userId, $data);
                        $result[$type] = $res ? self::STATUS_SUCCESS : self::STATUS_FAILED;
                    } elseif (empty($data) && in_array($type, SendDataServer::ALLOW_IS_EMPTY)) {
                        $result[$type] = self::STATUS_SUCCESS;
                    } else {
                        $result[$type] = self::STATUS_FAILED;
                    }
                }
            } catch (\Exception $e) {
                $result[$type] = self::STATUS_FAILED;

                $eMsg = $e->getFile() . ':' . $e->getLine() . '=>' . $e->getMessage();

                DingHelper::notice(['e' => $eMsg, 'type' => $type, 'user_id' => $userId, 'taskNo' => $taskNo], '风控数据上传错误', DingHelper::AT_SOLIANG);
            }
        }

        if (!$result) {
            return $this->resultFail('send data cannot be empty');
        }

        if (isset($task)) {
            $finishType = array_keys(array_where($result, function ($item) {
                return $item == self::STATUS_SUCCESS;
            }));

            TaskDataServer::server()->sendDataFinish($task, $finishType);
        }

        return $this->resultSuccess($result, 'ok');
    }

    public function common(SendDataRule $rule)
    {
        $params = $this->request->all();
        if (!$rule->validate($rule::SCENARIO_SEND_COMMON, $params)) {
            return $this->resultFail($rule->getError());
        }

        $server = SendDataServer::server();
        $result = [];
        foreach (SendDataServer::COMMON_DATA_TYPE as $type) {
            try {
                $method = "save" . Str::studly(Str::lower($type));

                if ($this->request->has($type) && is_callable([$server, $method])) {
                    $data = $this->request->get($type);
                    if (!is_array($data)) {
                        return $this->resultFail(array_get(TaskData::TYPE, $type, '') . 'field is not formatted correctly');
                    }
                    if ($data) {
                        $res = $server->$method($data);
                        $result[$type] = $res ? self::STATUS_SUCCESS : self::STATUS_FAILED;
                    } elseif (empty($data) && in_array($type, SendDataServer::ALLOW_IS_EMPTY)) {
                        $result[$type] = self::STATUS_SUCCESS;
                    } else {
                        $result[$type] = self::STATUS_FAILED;
                    }
                }
            } catch (\Exception $e) {
                $result[$type] = self::STATUS_FAILED;
                $eMsg = $e->getFile() . ':' . $e->getLine() . '=>' . $e->getMessage();

                DingHelper::notice(['e' => $eMsg, 'type' => $type, 'data' => $data], '风控数据上传错误-common', DingHelper::AT_SOLIANG);
            }
        }

        if (!$result) {
            return $this->resultFail('send data cannot be empty');
        }

        return $this->resultSuccess($result, 'ok');
    }
}
