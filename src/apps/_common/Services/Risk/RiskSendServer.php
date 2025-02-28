<?php

namespace Common\Services\Risk;

use Common\Jobs\Risk\RiskSendDataCommonJob;
use Common\Jobs\Risk\RiskSendDataJob;
use Common\Models\SystemApprove\SystemApproveTask;
use Common\Services\BaseService;
use JMD\Libs\Risk\RiskSend;

class RiskSendServer extends BaseService
{
    // 需要额外隔离上传的数据类型
    const NEED_SEGREGATE_DATA_SEND_TYPE = [
        RiskSend::TYPE_USER_APPLICATION,
        RiskSend::TYPE_USER_CONTACTS_TELEPHONE,
        RiskSend::TYPE_USER_PHONE_HARDWARE,
        RiskSend::TYPE_USER_POSITION,
        RiskSend::TYPE_USER_PHONE_PHOTO,
        RiskSend::TYPE_USER_SMS,
    ];

    public function register($userId)
    {
        $type = [
            RiskSend::TYPE_USER,
        ];

        return $this->sendByType($userId, $type);
    }

    public function createOrder($userId)
    {
        $type = [
            RiskSend::TYPE_USER,
            RiskSend::TYPE_USER_INFO,
            RiskSend::TYPE_USER_WORK,
            RiskSend::TYPE_BANK_CARD,
            RiskSend::TYPE_ORDER,
            RiskSend::TYPE_ORDER_DETAIL,
        ];

        return $this->sendByType($userId, $type);
    }

    public function orderCancel($userId)
    {
        $type = [
            RiskSend::TYPE_ORDER,
        ];

        return $this->sendByType($userId, $type);
    }

    public function approveFinish($userId)
    {
        $type = [
            RiskSend::TYPE_ORDER,
            RiskSend::TYPE_MANUAL_APPROVE_LOG,
        ];

        return $this->sendByType($userId, $type);
    }

    public function paid($userId)
    {
        $type = [
            RiskSend::TYPE_ORDER,
            RiskSend::TYPE_REPAYMENT_PLAN,
            RiskSend::TYPE_BANK_CARD,
        ];

        return $this->sendByType($userId, $type);
    }

    public function repay($userId)
    {
        $type = [
            RiskSend::TYPE_ORDER,
            RiskSend::TYPE_REPAYMENT_PLAN,
        ];

        return $this->sendByType($userId, $type);
    }

    public function overdue($userId)
    {
        $type = [
            RiskSend::TYPE_ORDER,
            RiskSend::TYPE_REPAYMENT_PLAN,
        ];

        return $this->sendByType($userId, $type);
    }

    public function collection($userId)
    {
        $type = [
            RiskSend::TYPE_COLLECTION_RECORD,
        ];

        return $this->sendByType($userId, $type);
    }

    public function orderBad($userId)
    {
        $type = [
            RiskSend::TYPE_USER,
            RiskSend::TYPE_ORDER,
            RiskSend::TYPE_COLLECTION_RECORD,
        ];

        return $this->sendByType($userId, $type);
    }

    /**
     * 根据类型上传风控数据
     * @param $userId
     * @param $type
     * @param SystemApproveTask|null $task
     * @return bool
     * @throws \Exception
     */
    public function sendByType($userId, $type, SystemApproveTask $task = null)
    {
        if (isset($task) && $task->user_id != $userId) {
            throw new \Exception('风控数据发送：userId 与 task 所属user不一致');
        }

        $segregateDataSendType = array_intersect(self::NEED_SEGREGATE_DATA_SEND_TYPE, $type);

        if ($segregateDataSendType) {
            RiskSendServer::server()->sendQueue($userId, $segregateDataSendType, $task);
        }

        $type = array_diff($type, $segregateDataSendType);

        if ($type) {
            RiskSendServer::server()->sendQueue($userId, $type, $task);
        }

        return true;
    }

    public function sendQueue($userId, $type = null, $task = null, $delay = 1)
    {
        return dispatch((new RiskSendDataJob($userId, $type, $task))->delay($delay));
    }

    public function sendCommonQueue($merchantId, $type = null, $delay = 1)
    {
        return dispatch((new RiskSendDataCommonJob($merchantId, $type))->delay($delay));
    }
}
