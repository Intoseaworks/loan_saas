<?php

namespace Common\Events\Report;

use Common\Events\Event;

/**
 * Class NbfcReportEvent
 * NBFC 上报
 * @package Common\Events\Order
 */
class NbfcReportEvent extends Event
{
    /**
     * 订单ID
     */
    protected $orderId;

    protected $node;

    /**
     * OrderAgreementEvent constructor.
     * @param $orderId
     * @param $reportNode
     */
    public function __construct($orderId, $reportNode)
    {
        $this->orderId = $orderId;
        $this->node = $reportNode;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function getNode()
    {
        return $this->node;
    }
}
