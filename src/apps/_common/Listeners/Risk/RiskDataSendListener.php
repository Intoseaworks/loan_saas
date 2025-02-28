<?php

namespace Common\Listeners\Risk;

use Common\Events\Risk\RiskDataSendEvent;
use Common\Models\Order\Order;
use Common\Services\Risk\RiskSendServer;
use Common\Utils\MerchantHelper;
use Illuminate\Contracts\Queue\ShouldQueue;

class RiskDataSendListener implements ShouldQueue
{
    public $queue = 'system-approve';

    protected $userId;
    protected $node;

    public function __construct()
    {
        MerchantHelper::clearMerchantId();
    }

    /**
     * @param RiskDataSendEvent $event
     * @throws \Exception
     */
    public function handle(RiskDataSendEvent $event)
    {
        $userId = $event->getUserId();
        $this->node = $node = $event->getNode();
        if (!$userId) {
            $orderId = $event->getOrderId();
            if (!$orderId) {
                throw new \Exception("RiskDataSendListener event参数不正确。user_id:{$userId} order_id:{$orderId} node:{$node}");
            }

            $order = (new Order)->getOne($orderId);
            if (!$order) {
                throw new \Exception("RiskDataSendListener 订单ID不存在。user_id:{$userId} order_id:{$orderId} node:{$node}");
            }
            $userId = $order->user_id;
        }

        $this->userId = $userId;

        $this->send();
    }

    /**
     * @throws \Exception
     */
    protected function send()
    {
        $userId = $this->userId;
        switch ($this->node) {
            /** 节点：用户注册 */
            case RiskDataSendEvent::NODE_USER_REGISTER:
                RiskSendServer::server()->register($userId);
                break;
            /** 节点：订单创建 */
            case RiskDataSendEvent::NODE_ORDER_CREATE:
                RiskSendServer::server()->createOrder($userId);
                break;
            /** 节点：订单取消 */
            case RiskDataSendEvent::NODE_ORDER_CANCEL:
                RiskSendServer::server()->orderCancel($userId);
                break;
            /** 节点：审批完成(通过|拒绝|取消) */
            case RiskDataSendEvent::NODE_APPROVE_FINISH:
                RiskSendServer::server()->approveFinish($userId);
                break;
            /** 节点：放款 */
            case RiskDataSendEvent::NODE_ORDER_PAID:
                RiskSendServer::server()->paid($userId);
                break;
            /** 节点：还款 */
            case RiskDataSendEvent::NODE_ORDER_REPAY:
                RiskSendServer::server()->repay($userId);
                break;
            /** 节点：逾期 */
            case RiskDataSendEvent::NODE_ORDER_OVERDUE:
                RiskSendServer::server()->overdue($userId);
                break;
            /** 节点：催收 */
            case RiskDataSendEvent::NODE_ORDER_COLLECTION:
                RiskSendServer::server()->collection($userId);
                break;
            /** 节点：坏账 */
            case RiskDataSendEvent::NODE_ORDER_BAD:
                RiskSendServer::server()->orderBad($userId);
                break;
            default:
                throw new \Exception("node节点未定义:{$this->node} user_id:{$this->userId}");
        }
    }
}
