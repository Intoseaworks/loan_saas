<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/10
 * Time: 15:02
 */

namespace Common\Services\Push;


use Common\Models\Order\Order;
use Common\Models\User\User;
use Common\Services\BaseService;

class PushCheckService extends BaseService
{

    const ORDER_IN_PASS = 'orderInPass';

    public $params;

    public $userId;
    public $orderId;

    public $user;
    public $order;

    public $hasUser = true;
    public $hasOrder = true;

    public function __construct($params = [])
    {
        $this->params = $params;
        $this->check();
    }

    public function check()
    {
        // 用户参数校验
        if ($this->userId = array_get($this->params, 'userId')) {
            $this->user = User::model()->getOne($this->userId);
        }
        // 订单参数检验
        if ($this->orderId = array_get($this->params, 'orderId')) {
            $this->order = Order::model()->getOne($this->orderId);
        }
    }

    /**
     * 审核通过check
     *
     * @param $userId
     * @param $amount
     * @return bool
     */
    public function orderInPass()
    {
        if (!$this->order) {
            return false;
        }
        // 不在审核通过待签约状态不推送
        if (!in_array($this->order->status, [
            Order::STATUS_SYSTEM_PASS,
            Order::STATUS_MANUAL_PASS,
        ])) {
            return false;
        }
        return true;
    }

}
