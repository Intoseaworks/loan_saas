<?php

namespace Common\Jobs\Risk;

use Common\Jobs\Job;
use Common\Models\SystemApprove\SystemApproveTask;
use Common\Redis\Risk\RiskSendTaskDataRedis;
use Common\Services\Risk\RiskSendServer;
use Common\Services\Risk\RiskServer;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\MerchantHelper;
use JMD\Libs\Risk\RiskSend;

class RiskSendDataJob extends Job
{
    public $queue = 'system-approve';

    public $tries = 3;

    protected $userId;
    protected $task;
    protected $type;

    public function __construct($userId, $type = null, SystemApproveTask $task = null)
    {
        $this->userId = $userId;
        $this->type = $type;
        $this->task = $task;

        if (is_null($type)) {
            $this->type = RiskSend::ALL_TYPE;
        }
    }

    /**
     * @return bool
     */
    public function handle()
    {
        return;
        try {
            MerchantHelper::clearMerchantId();

            $taskNo = null;
            if (isset($this->task)) {
                $task = $this->task->refresh();

                if ($task->user_id != $this->userId) {
                    throw new \Exception('用户任务对应错误');
                }

                if ($task->status != SystemApproveTask::STATUS_CREATE) {
                    return true;
                }
                $taskNo = $task->task_no;
            }

            $res = RiskServer::server()->sendDataAll($this->userId, $this->type, $taskNo);

            if (!$res->isSuccess()) {
                $this->retry($this->type);
                throw new \Exception($res->getMsg(), $res->getCode());
            }

            $data = $res->getData();
            $needRetryType = array_keys(array_filter($data, function ($item) {
                return $item != RiskServer::SEND_DATA_STATUS_SUCCESS;
            }));

            $this->retry($needRetryType);
        } catch (\Exception $e) {
            DingHelper::notice([
                'merchant_id' => MerchantHelper::getMerchantId(),
                'user_id' => $this->userId ?? '',
                'taskId' => optional($this->task)->id,
                'type' => $this->type,
                'msg' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
            ], '机审数据上传队列异常', DingHelper::AT_SOLIANG);
        }
    }

    protected function retry($type)
    {
        if (!$type) {
            return false;
        }

        $redisKey = $this->userId . '_' . optional($this->task)->id;
        $surplusTypes = RiskSendTaskDataRedis::redis()->add($redisKey, $type);

        if (!$surplusTypes) {
            return false;
        }
        // 失败次数未超限的类型进行重试
        return RiskSendServer::server()->sendQueue($this->userId, $surplusTypes, $this->task, 30);
    }
}
