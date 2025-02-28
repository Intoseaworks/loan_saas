<?php

namespace Common\Events\Order;

use Common\Events\Event;
use Common\Utils\MerchantHelper;

/**
 * Class OrderServicesPushEvent
 * 订单状态流转印牛服务推送
 * @package Common\Events\Order
 */
class OrderServicesPushEvent extends Event
{
    /** 类型：创建订单 */
    const TYPE_ORDER_CREATE = 'order_create';
    /** 类型：审批拒绝 */
    const TYPE_ORDER_REJECT = 'order_reject';
    /** 类型：取消订单 */
    const TYPE_ORDER_CANCEL = 'order_cancel';
    /** 类型：订单放款 */
    const TYPE_ORDER_REMIT = 'order_remit';
    /** 类型：订单还款 */
    const TYPE_ORDER_REPAY = 'order_repay';
    /** 类型 */
    const TYPE = [
        self::TYPE_ORDER_CREATE => '创建订单',
        self::TYPE_ORDER_REJECT => '审批拒绝',
        self::TYPE_ORDER_CANCEL => '取消订单',
        self::TYPE_ORDER_REMIT => '订单放款',
        self::TYPE_ORDER_REPAY => '订单还款',
    ];

    protected $orderId;
    protected $type;
    protected $merchantId;

    /**
     * OrderFlowPushEvent constructor.
     * @param $orderId
     * @param $pushType
     * @param $merchantId
     * @throws \Exception
     */
    public function __construct($orderId, $pushType, $merchantId = null)
    {
        //停止更新
        return true;
        $this->orderId = $orderId;
        $this->type = $pushType;
        $this->merchantId = $merchantId ?? MerchantHelper::getMerchantId();
        if (!in_array($this->type, array_keys(self::TYPE))) {
            throw new \Exception('OrderServicesPushEvent 推送类型有误');
        }
    }

    public function getType()
    {
        return $this->type;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function getMerchantId()
    {
        return $this->merchantId;
    }
}
