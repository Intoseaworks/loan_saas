<?php

namespace Risk\Common\Services\SystemApprove;

use Common\Utils\DingDing\DingHelper;
use Common\Utils\MerchantHelper;
use Risk\Common\Models\Business\Order\Order;

class SystemApproveSandboxServer extends SystemApproveServer
{
    /**
     * 久未更新
     * @param Order $order
     * @return bool
     * @throws \Exception
     */
    public function approveOrder(Order $order)
    {
        MerchantHelper::setMerchantId($order->app_id);

        try {
            $ruleRes = $this->basicRulePasses($order, true);
            return true;
        } catch (\Exception $e) {
            DingHelper::notice(
                json_encode([
                    'file' => $e->getFile() . ":" . $e->getLine(),
                    'order_id' => $order->id,
                    'rule_result' => $ruleRes ?? ''
                ]),
                '沙盒模拟机审异常 - ' . $e->getMessage() . '-' . app()->environment()
            );
            return false;
        }
    }
}
