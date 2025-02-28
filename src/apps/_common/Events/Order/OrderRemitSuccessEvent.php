<?php

namespace Common\Events\Order;

use Common\Events\Event;
use Common\Models\Order\Order;

/**
 * Class OrderAgreementEvent
 * 出款成功之后异步事件处理
 * 1：检查预警值&发送预警通知
 * @package Common\Events\Order
 */
class OrderRemitSuccessEvent extends Event
{
    /**
     * 订单ID
     */
    protected $orderId;

    protected $merchantId;

    /**
     * OrderAutoDaifuEvent constructor.
     * @param $orderId
     * @param $merchantId
     */
    public function __construct($orderId, $merchantId)
    {
        $this->orderId = $orderId;
        $this->merchantId = $merchantId;
    }

    /**
     *
     * @return Order
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    public function getMerchantId()
    {
        return $this->merchantId;
    }
}
