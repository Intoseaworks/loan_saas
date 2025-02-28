<?php

namespace Common\Events\Order;

use Common\Events\Event;
use Common\Models\Order\Order;
use Common\Utils\DingDing\DingHelper;

/**
 * Class OrderFlowPushEvent
 * 订单状态流转消息推送
 * @package Common\Events\Order
 */
class OrderFlowPushEvent extends Event
{
    /** 类型：待确认借款 */
    const TYPE_WAIT_LOAN = 'wait_loan';
    /** 类型：审批中 */
    const TYPE_INTO_APPROVE = 'into_approve';
    /** 类型：重新提交资料 */
    const TYPE_REPLENISH = 'replenish';
    /** 类型：人审通过电审中 */
    const TYPE_APPROVE_TO_CALL = 'approve_to_call';
    /** 类型：审批通过放款中 */
    const TYPE_APPROVE_PASS = 'approve_pass';
    /** 类型：线下放款成功待取款 */
    const TYPE_DRAW_MONEY = 'draw_money';
    /** 类型：放款失败 */
    const TYPE_PAY_FAIL = 'pay_fail';
    /** 类型：待还款(出款成功) */
    const TYPE_PAY_SUCCESS = 'pay_success';
    /** 类型：已逾期 */
    const TYPE_OVERDUE = 'overdue';
    /** 类型：已还款 */
    const TYPE_REPAY_FINISH = 'repay_finish';
    /** 类型：到期还款提醒 */
    const TYPE_EXPIRATION_REMINDER = 'expiration_reminder';
    /** 类型：代扣失败 */
    const TYPE_DAIKOU_FAILED = 'daikou_failed';
    /** 类型：还款减免 */
    const TYPE_REPAY_REDUCTION = 'repay_reduction';
    /** 类型：展期成功 */
    const TYPE_RENEWAL_SUCCESS = 'renewal_success';
    /** 类型 */
    const TYPE = [
        self::TYPE_WAIT_LOAN => '待确认借款',
        self::TYPE_INTO_APPROVE => '审批中',
        self::TYPE_REPLENISH => '重新提交资料',
        self::TYPE_APPROVE_TO_CALL => '人审通过电审中',
        self::TYPE_APPROVE_PASS => '审批通过放款中',
        self::TYPE_PAY_FAIL => '放款失败',
        self::TYPE_PAY_SUCCESS => '待还款',
        self::TYPE_OVERDUE => '已逾期',
        self::TYPE_REPAY_FINISH => '已还款',
        self::TYPE_EXPIRATION_REMINDER => '到期还款提醒',
        self::TYPE_DAIKOU_FAILED => '扣款失败提醒',
        self::TYPE_REPAY_REDUCTION => '还款减免',
        self::TYPE_RENEWAL_SUCCESS => '展期成功',
        self::TYPE_DRAW_MONEY => '线下放款成功待取款',
    ];
    /**
     * @var Order 订单模型
     */
    protected $orderId;
    protected $type;
    protected $userId;

    /**
     * OrderFlowPushEvent constructor.
     * @param $order
     * @param $pushType
     * @param $userId
     */
    public function __construct($order, $pushType, $userId = null)
    {
        $this->orderId = $order->id;
        $this->type = $pushType;
        $this->userId = $userId;
        if ($order) {
            $this->userId = $order->user_id;
        }
        if (!in_array($this->type, array_keys(self::TYPE))) {
            DingHelper::notice([
                'user_id' => $this->userId ?? '',
                'order_id' => $this->orderId ?? '',
                'type' => $this->type ?? '',
            ], 'push事件推送类型有误');
            return false;
            //throw new \Exception('OrderFlowPushEvent 推送类型有误');
        }
    }

    /**
     *
     * @return Order
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getUserId()
    {
        return $this->userId;
    }
}
