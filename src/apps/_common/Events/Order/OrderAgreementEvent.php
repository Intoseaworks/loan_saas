<?php

namespace Common\Events\Order;

use Common\Events\Event;
use Common\Models\Order\Order;
use Common\Models\Order\OrderSignDoc;

/**
 * Class OrderAgreementEvent
 * 合同协议生成事件
 * @package Common\Events\Order
 */
class OrderAgreementEvent extends Event
{
    /**
     * 订单ID
     */
    protected $orderId;

    /**
     * 需要生成的协议类型
     */
    public $type;

    public $signType;

    /**
     * OrderAgreementEvent constructor.
     * @param $orderId
     * @param $signType
     * @param $type
     */
    public function __construct($orderId, $type = null, $signType = OrderSignDoc::TYPE_ESIGN)
    {
        $this->orderId = $orderId;
        $this->type = $type;
        $this->signType = $signType;
    }

    /**
     *
     * @return Order
     */
    public function getOrderId()
    {
        return $this->orderId;
    }
}
