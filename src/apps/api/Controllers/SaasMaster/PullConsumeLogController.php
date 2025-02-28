<?php

namespace Api\Controllers\SaasMaster;

use Api\Services\SaasMaster\PullConsumeLogServer;
use Common\Response\ServicesApiBaseController;

class PullConsumeLogController extends ServicesApiBaseController
{
    public function pullOrderData()
    {
        if (!$this->validateSign()) {
            return $this->resultFail('验签失败');
        }

        $timeStart = array_get($this->params, 'time_start');
        $timeEnd = array_get($this->params, 'time_end');

        $jsonData = PullConsumeLogServer::server()->pullOrderData($timeStart, $timeEnd);

        return $this->resultSuccessOrigin($jsonData, '获取成功');
    }

    public function pullSystemApproveData()
    {
        if (!$this->validateSign()) {
            return $this->resultFail('验签失败');
        }

        $timeStart = array_get($this->params, 'time_start');
        $timeEnd = array_get($this->params, 'time_end');

        $jsonData = PullConsumeLogServer::server()->pullSystemApproveData($timeStart, $timeEnd);

        return $this->resultSuccessOrigin($jsonData, '获取成功');
    }
}
