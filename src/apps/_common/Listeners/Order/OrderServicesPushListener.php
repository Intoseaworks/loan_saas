<?php

namespace Common\Listeners\Order;

use Common\Events\Order\OrderServicesPushEvent;
use Common\Models\Order\Order;
use Common\Models\User\User;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Services\DataPushRequestHelper;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderServicesPushListener implements ShouldQueue
{
    /**
     * @var Order
     */
    protected $orderId;

    public function handle(OrderServicesPushEvent $event)
    {
        //停止更新
        return true;
        MerchantHelper::setMerchantId($event->getMerchantId());
        $this->orderId = $event->getOrderId();
        $type = $event->getType();
        try{
            $this->push($type);
        } catch (\Exception $e){
            DingHelper::notice([
                'order_id' => $this->orderId,
                'type' => $type,
                'e' => EmailHelper::warpException($e)
            ] , '数据上报异常 - ' . app()->environment());
        }
        return true;
    }

    public function push($type)
    {
        switch ($type) {
            case OrderServicesPushEvent::TYPE_ORDER_CREATE:
                $this->orderCreate();
                $this->riskData();
                break;
            case OrderServicesPushEvent::TYPE_ORDER_REJECT:
                $this->orderReject();
                break;
            case OrderServicesPushEvent::TYPE_ORDER_CANCEL:
                $this->orderCancel();
                break;
            case OrderServicesPushEvent::TYPE_ORDER_REMIT:
                $this->orderRemit();
                break;
            case OrderServicesPushEvent::TYPE_ORDER_REPAY:
                $this->orderRepay();
                break;
        }
    }

    /**
     * 待确认借款
     */
    protected function orderCreate()
    {
        $order = Order::model()->getOne($this->orderId);
        if (!$order) {
            return $this->sendErrorEmail('订单id查找用户为空');
        }
        $user = User::model()->getOne($order->user_id);
        $data = [
            'user' => $user->toArray(),
            'user_info' => $user->userInfo->toArray(),
            'user_work' => $user->userWork->toArray(),
            'user_contact' => $user->userContacts->toArray(),
            'user_auth' => $user->userAuths->toArray(),
            'order' => $order->toArray(),
            'order_detail' => $order->orderDetails->toArray(),
            'order_log' => $order->orderLog->toArray(),
        ];
        return DataPushRequestHelper::helper()->push(OrderServicesPushEvent::TYPE_ORDER_CREATE, $data);
    }

    public function riskData()
    {
        $order = Order::model()->getOne($this->orderId);
        if (!$order) {
            return $this->sendErrorEmail('订单id查找用户为空');
        }
        $user = User::model()->getOne($order->user_id);
        $data = [
            'user' => $user->toArray(),
            'user_position' => $user->userPosition ? $user->userPosition->toArray() : '',
            'user_application' => $user->userApplications->toArray(),
            'user_contacts_telephone' => $user->userContactsTelephones->toArray(),
            'user_phone_hardware' => $user->userPhoneHardwares->toArray(),
        ];
        return DataPushRequestHelper::helper()->push(OrderServicesPushEvent::TYPE_ORDER_CREATE, $data);
    }

    /**
     * 邮件报错
     * @param string $msg
     * @return null
     */
    protected function sendErrorEmail($msg = '')
    {
        $backtrace = debug_backtrace();
        $functionName = optional($backtrace[1])['function'];
        EmailHelper::send($functionName . "调用错误\norderId:{$this->orderId}\n" . $msg, '订单状态流转services推送错误');
        return null;
    }

    protected function orderReject()
    {
        $order = Order::model()->getOne($this->orderId);
        if (!$order) {
            return $this->sendErrorEmail('订单id查找用户为空');
        }
        $data = [
            'order' => $order->toArray(),
            'order_log' => $order->orderLog->toArray(),
            'manual_approve_log' => $order->manualApproveLog->toArray(),
        ];
        return DataPushRequestHelper::helper()->push(OrderServicesPushEvent::TYPE_ORDER_REJECT, $data);
    }

    public function orderCancel()
    {
        $order = Order::model()->getOne($this->orderId);
        if (!$order) {
            return $this->sendErrorEmail('订单id查找用户为空');
        }
        $data = [
            'order' => $order->toArray(),
            'order_log' => $order->orderLog->toArray(),
            'manual_approve_log' => $order->manualApproveLog->toArray(),
            'repayment_plan' => $order->lastRepaymentPlan->toArray(),
        ];
        return DataPushRequestHelper::helper()->push(OrderServicesPushEvent::TYPE_ORDER_CANCEL, $data);
    }

    public function orderRemit()
    {
        $order = Order::model()->getOne($this->orderId);
        if (!$order) {
            return $this->sendErrorEmail('订单id查找用户为空');
        }
        $data = [
            'order' => $order->toArray(),
            'order_log' => $order->orderLog->toArray(),
            'repayment_plan' => $order->lastRepaymentPlan->toArray(),
            'bank_card' => $order->user->bankCard->toArray(),
        ];
        return DataPushRequestHelper::helper()->push(OrderServicesPushEvent::TYPE_ORDER_REMIT, $data);
    }

    public function orderRepay()
    {
        $order = Order::model()->getOne($this->orderId);
        if (!$order) {
            return $this->sendErrorEmail('订单id查找用户为空');
        }
        $data = [
            'order' => $order->toArray(),
            'order_log' => $order->orderLog->toArray(),
            'repayment_plan' => $order->lastRepaymentPlan->toArray(),
            'collection_record' => isset($order->collection) ? $order->collection->collectionRecords->toArray() : [],
        ];
        return DataPushRequestHelper::helper()->push(OrderServicesPushEvent::TYPE_ORDER_REPAY, $data);
    }

}
