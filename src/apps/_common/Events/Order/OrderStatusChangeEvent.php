<?php

namespace Common\Events\Order;

use Common\Events\Event;
use Common\Models\Order\Order;

/**
 * Class OrderStatusChangeEvent
 * 订单状态流转记录日志
 * @package Common\Events\Order
 */
class OrderStatusChangeEvent extends Event
{


    public $order;
    public $toStatus;
    public $content;
    public $name;


    /**
     * OrderStatusChangeEvent constructor.
     * @param Order $order
     * @param $toStatus
     * @param string $content
     * @param string $name
     */
    public function __construct(Order $order, $toStatus, $content = '', $name = '')
    {
        $this->order = $order;
        $this->toStatus = $toStatus;
        $this->content = $content;
        $this->name = $name;
    }


}
