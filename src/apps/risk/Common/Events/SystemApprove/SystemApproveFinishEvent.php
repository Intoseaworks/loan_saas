<?php

namespace Risk\Common\Events\SystemApprove;

use Common\Events\Event;

class SystemApproveFinishEvent extends Event
{
    private $taskId;
    private $orderId;
    private $appId;

    public function __construct($taskId, $appId, $orderId)
    {
        $this->taskId = $taskId;
        $this->appId = $appId;
        $this->orderId = $orderId;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function getTaskId()
    {
        return $this->taskId;
    }
}
