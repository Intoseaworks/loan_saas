<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-11
 * Time: 10:54
 */

namespace Approve\Admin\Services\Order;


use Approve\Admin\Services\Approval\CallApproveService;
use Approve\Admin\Services\Approval\FirstApproveService;
use Approve\Admin\Services\CommonService;
use Common\Models\Approve\ApprovePool;
use Common\Models\Approve\ApproveResultSnapshot;
use Common\Models\Order\Order;
use Common\Models\Order\OrderLog;
use Common\Traits\GetInstance;
use Common\Utils\Code\OrderStatus;

class ApproveOrderService
{
    use GetInstance;

    /**
     * 审批数据
     * @var array
     */
    protected $data = [];

    /**
     * 初始化快照
     * @var array
     */
    protected $initSnapshoot = [
        // 初审快照
        ApproveResultSnapshot::TYPE_FIRST_APPROVE => [],
        // 电审快照
        ApproveResultSnapshot::TYPE_CALL_APPROVE => [],
    ];

    /**
     * 需要在审批池移除的数据
     * @var array
     */
    protected $needClear = [];

    /**
     * @return array
     */
    public function getOrder()
    {
        $returnData = [
            'data' => [],
            'clear' => [],
            'initSnapshoot' => [],
        ];

        $this->getFirstOrder();
        $this->getCallOrder();

        if ($this->data) {

            foreach ($this->data as $item) {
                // 初审的时候才生成默认快照
                if ($item['order_status'] == OrderStatus::FIRST_APPROVAL) {
                    $firstJson = json_encode(FirstApproveService::getInstance($item['order_id'])->initSnapshoot(), JSON_UNESCAPED_UNICODE);
                    $callJson = json_encode(CallApproveService::getInstance($item['order_id'])->initSnapshoot(), JSON_UNESCAPED_UNICODE);
                    $this->initSnapshoot[ApproveResultSnapshot::TYPE_FIRST_APPROVE][$item['order_id']] = $firstJson;
                    $this->initSnapshoot[ApproveResultSnapshot::TYPE_CALL_APPROVE][$item['order_id']] = $callJson;
                }
            }

            $returnData = [
                'data' => $this->data,
                'clear' => $this->needClear,
                'initSnapshoot' => $this->initSnapshoot,
            ];
        }

        return $returnData;
    }

    /**
     * @return bool
     */
    public function getFirstOrder()
    {
        $orders = Order::select($this->getSelectColumns())
            ->with(['user' => function ($query) {
                $query->select(['telephone', 'id']);
            }])
            ->where(['approve_push_status' => Order::PUSH_STATUS_WAITING])
            ->where(['manual_check' => Order::MANUAL_CHECK_REQUIRE])
            ->whereIn('status', [Order::STATUS_WAIT_MANUAL_APPROVE])
            ->get()
            ->keyBy('id');

        return $this->warpData($orders, ApprovePool::ORDER_FIRST_GROUP);
    }

    /**
     * @return array
     */
    protected function getSelectColumns()
    {
        $columns = [
            'id',
            'user_id',
            'merchant_id',
            'status',
            'order_no',
            'system_time',
            'created_at',
            'manual_time',
        ];
        return $columns;
    }

    /**
     * @param $orders
     * @param $type
     * @return bool
     */
    protected function warpData($orders, $type)
    {
        $data = [];
        /** @var Order $order */
        foreach ($orders as $order) {
            CommonService::getInstance()->getRealOrderStatus($order);
            // 查看审批池对应的订单是否正在审批中.
            //因为会存在一种情况:审批提交后,业务把订单状态修改,定时任务刚好运行.这时候会推送数据过去.然后审批系统提交后修改审批单状态.会覆盖推送的数据.
            //一般推送和并行同时发生就会出现这种情况.比如:初审通过(26->41),业务库那边的订单状态已经变为41.刚好订单推送任务又开始运行.这时候就会把订单推送到业务库插入数据
            // 审批系统请求业务库后根据业务返回状态码修改approve_pool状态.会把刚刚推送的订单的approve_pool.status变为初审通过,而不是待审批
            $processing = ApprovePool::where(['order_id' => $order->id, 'status' => ApprovePool::STATUS_CHECKING])->first();
            if ($processing) {
                continue;
            }

            $temp = [
                'order_id' => $order->id,
                'merchant_id' => $order->merchant_id,
                'type' => $type,
                'grade' => $this->getOrderGrade($order, $type),
                'order_no' => $order->order_no,
                'telephone' => $order->user->telephone ?? '',
                'user_id' => $order->user_id ?? 0,
                'order_status' => $this->getOrderStatus($order->_status),
                'order_type' => 'cashnow',
                'status' => $this->getApproveStatus($order),
                'order_created_time' => time(),
                'manual_time' => $order->manual_time ? strtotime($order->manual_time) : time(),
                'risk_pass_time' => $this->getRiskPassTime($order),
            ];
            $data[] = $temp;
        }

        $this->data = array_merge($this->data, $data);
        return true;
    }

    /**
     * @param Order $order
     * @param $type
     * @return int
     */
    protected function getOrderGrade(Order $order, $type)
    {
        $grade = 0;
        if ($type == ApprovePool::ORDER_FIRST_GROUP) {
            if ($order->_status == Order::STATUS_REPLENISH) {
                $grade = ApprovePool::GRADE_SUPPLEMENT_APPROVE;
            } else {
                $grade = ApprovePool::GRADE_FIRST_APPROVE;
            }
        }

        if ($type == ApprovePool::ORDER_CALL_GROUP) {
            if ($order->_status == Order::STATUS_WAIT_TWICE_CALL_APPROVE) {
                /** @var Order $order */
                $callNum = OrderLog::model()->getTwiceCallNum($order->id);
                // 电审二审一次
                $grade = ApprovePool::GRADE_SECOND_CALL_APPROVE;

                // 电审二审二次
                if ($callNum > 1 && $callNum <= 2) {
                    $grade = ApprovePool::GRADE_SECOND_CALL_TWICE_APPROVE;
                }

            } elseif ($order->_status == Order::STATUS_WAIT_CALL_APPROVE) {
                $grade = ApprovePool::GRADE_CALL_APPROVE;
            }
        }

        return $grade;
    }

    /**
     * @param $orderStatus
     * @return int|mixed
     */
    protected function getOrderStatus($orderStatus)
    {
        $list = [
            Order::STATUS_WAIT_MANUAL_APPROVE => OrderStatus::FIRST_APPROVAL,
            Order::STATUS_REPLENISH => OrderStatus::FIRST_APPROVAL_SUPPLEMENT,
            Order::STATUS_WAIT_TWICE_CALL_APPROVE => OrderStatus::CALL_APPROVAL_SECOND,
            Order::STATUS_WAIT_CALL_APPROVE => OrderStatus::CALL_APPROVAL,
        ];

        return $list[$orderStatus] ?? 0;
    }

    /**
     * @param Order $order
     * @return int
     */
    protected function getApproveStatus(Order $order)
    {
        if ($order->_status == Order::STATUS_WAIT_TWICE_CALL_APPROVE) {
            if ($this->checkSecondCall($order->id)) {
                return ApprovePool::STATUS_WAITING;
            } else {
                return ApprovePool::STATUS_NOT_CONDITION;
            }
        }

        return ApprovePool::STATUS_WAITING;
    }

    /**
     * 电二审是否可以审批
     *
     * @param $orderId
     * @return bool
     */
    public function checkSecondCall($orderId)
    {
        /** @var Order $order */
        $order = Order::model()->getOne($orderId);
        $orderLogModel = OrderLog::model();
        if ($order && in_array($order->status, [Order::STATUS_WAIT_TWICE_CALL_APPROVE, Order::STATUS_WAIT_CALL_APPROVE])) {
            $callNum = $orderLogModel->getTwiceCallNum($order->order_id);
            $interval = time() - strtotime($order->manual_time);
            // 电审二审一次,间隔时间大于30分钟
            if ($callNum <= 1 && $interval > 30 * 60) {
                return true;
            }

            // 电审二审二次,间隔时间大于1小时
            if ($callNum <= 2 && $callNum > 1 && $interval > 60 * 60) {
                return true;
            }

            // 超过两次以上
            if ($callNum > 2) {
                return true;
            }
        }

        return false;
    }

    /**
     * 每次机审的时间,包含初审提交过的机审时间
     *
     * @param Order $order
     * @return int
     */
    protected function getRiskPassTime(Order $order)
    {
        if ($order->_status == Order::STATUS_WAIT_MANUAL_APPROVE) {
            # 无机审时，取订单创建时间
            return (int)strtotime($order->system_time) ?: (int)strtotime($order->created_at);
        }

        if ($order->_status == Order::STATUS_REPLENISH) {
            return time();
        }

        return $order->manual_time ? (int)strtotime($order->manual_time) : time();
    }

    /**
     * @return bool
     */
    protected function getCallOrder()
    {
        $orders = Order::select($this->getSelectColumns())
            ->with(['user' => function ($query) {
                $query->select(['telephone', 'id']);
            }])
            ->where(['approve_push_status' => Order::PUSH_STATUS_WAITING])
            ->where(['call_check' => Order::CALL_CHECK_REQUIRE])
            ->whereIn('status', [Order::STATUS_WAIT_CALL_APPROVE, Order::STATUS_WAIT_TWICE_CALL_APPROVE])
            ->get()
            ->keyBy('id');

        return $this->warpData($orders, ApprovePool::ORDER_CALL_GROUP);
    }

    /**
     * 推送数据后的操作
     *
     * @param array $orderIds
     */
    public function afterPush(array $orderIds)
    {
        Order::whereIn('id', $orderIds)->update(['approve_push_status' => Order::PUSH_STATUS_DONE]);
    }

    /**
     * 检查 电审二审/待补充资料是否可以审批
     */
    public function checkReachCondition()
    {
        $approveOrders = ApprovePool::where('status', ApprovePool::STATUS_NOT_CONDITION)->get();
        foreach ($approveOrders as $approveOrder) {
            echo "Order:".$approveOrder->order_id.PHP_EOL;
            switch ($approveOrder->grade) {
                case ApprovePool::GRADE_SECOND_CALL_APPROVE:
                case ApprovePool::GRADE_SECOND_CALL_TWICE_APPROVE:
                    if ($this->checkSecondCall($approveOrder->order_id)) {
                        $approveOrder->status = ApprovePool::STATUS_WAITING;
                        echo "To Waiting";
                    }
                    break;
            }

            $approveOrder->save();
        }
    }

}
