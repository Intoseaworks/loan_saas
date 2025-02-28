<?php

namespace Common\Events\Order;

use Common\Events\Event;
use Common\Models\Order\Order;

/**
 * Class OrderAgreementEvent
 * 订单创建之后异步事件处理
 * 1：检查订单剩余预警值&发送预警通知
 * @package Common\Events\Order
 */
class OrderCreateEvent extends Event
{
    /**
     * 订单ID
     */
    protected $orderId;

    protected $merchantId;

    protected $quality;

    /**
     * OrderAutoDaifuEvent constructor.
     * @param $orderId
     * @param $merchantId
     * @param $quality
     */
    public function __construct($orderId, $merchantId, $quality)
    {
        $this->orderId = $orderId;
        $this->merchantId = $merchantId;
        $this->quality = $quality;
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

    public function getQuality()
    {
        return $this->quality;
    }
}
