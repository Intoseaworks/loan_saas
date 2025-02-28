<?php

namespace Api\Controllers\Order;

use Admin\Rules\Order\RepaymentPlanRule;
use Admin\Services\Repayment\RenewalRepaymentServer;
use Api\Services\Order\OrderServer;
use Api\Services\Order\RenewalServer;
use Common\Response\ApiBaseController;

class RenewalController extends ApiBaseController
{
    /**
     * 获取订单续费信息
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getRenewalInfo()
    {
        $user = $this->identity();
        $orderId = $this->getParam('order_id');

        if (!$order = OrderServer::server()->canRenewal($user, $orderId)) {
            return $this->resultFail('订单状态不正确');
        }

        return $this->resultSuccess(RenewalServer::server()->getRenewalInfo($order));
    }

    /**
     * 申请续期
     * @param RepaymentPlanRule $rule
     * @return array
     */
    public function applyRenewal(RepaymentPlanRule $rule) {
        $user = $this->identity();
        $orderId = $this->getParam('order_id');

        if (!$order = OrderServer::server()->canRenewal($user, $orderId)) {
            return $this->resultFail('订单状态不正确');
        }
        $params = ['no'=>$order->lastRepaymentPlan->no];

        return $this->resultSuccess(RenewalRepaymentServer::server($params)->applyRenewal());
    }

    /**
     * 确认续期
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function confirmRenewal()
    {
        $user = $this->identity();
        $orderId = $this->getParam('order_id');

        if (!$order = OrderServer::server()->canRenewal($user, $orderId)) {
            return $this->resultFail('订单状态不正确');
        }

        $server = RenewalServer::server()->confirmRenewal($order);
        if (!$server->isSuccess()) {
            return $this->resultFail($server->getMsg());
        }

        return $this->resultSuccess(['trade_log_id' => $server->getData()->id], '已发起扣款，请耐心等待还款结果');
    }
}
