<?php

namespace Common\Events\Risk;

use Common\Events\Event;

/**
 * Class RiskDataSendEvent
 * 风控数据上传事件
 * @package Common\Events\Risk
 */
class RiskDataSendEvent extends Event
{
    /** 节点：用户注册 */
    const NODE_USER_REGISTER = 'USER_REGISTER';
    /** 节点：订单创建 */
    const NODE_ORDER_CREATE = 'ORDER_CREATE';
    /** 节点：订单取消 */
    const NODE_ORDER_CANCEL = 'ORDER_CANCEL';
    /** 节点：审批完成(通过|拒绝) */
    const NODE_APPROVE_FINISH = 'APPROVE_FINISH';
    /** 节点：放款 */
    const NODE_ORDER_PAID = 'ORDER_PAID';
    /** 节点：还款 */
    const NODE_ORDER_REPAY = 'ORDER_REPAY';
    /** 节点：逾期 */
    const NODE_ORDER_OVERDUE = 'ORDER_OVERDUE';
    /** 节点：催收 */
    const NODE_ORDER_COLLECTION = 'ORDER_COLLECTION';
    /** 节点：坏账 */
    const NODE_ORDER_BAD = 'ORDER_BAD';

    protected $userId;
    protected $orderId;
    protected $node;

    /**
     * RiskDataSendEvent constructor.
     * @param $userId
     * @param $node
     * @param null $orderId
     */
    public function __construct($userId, $node, $orderId = null)
    {
        $this->userId = $userId;
        $this->node = $node;
        $this->orderId = $orderId;
    }

    public function getNode()
    {
        return $this->node;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function getUserId()
    {
        return $this->userId;
    }
}
