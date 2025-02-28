<?php

namespace Risk\Common\Services\SystemApprove;

use Common\Utils\DingDing\DingHelper;
use Common\Utils\MerchantHelper;
use Common\Models\Order\Order;

class ComputeRiskDataSandboxServer extends ComputeRiskDataServer
{
    /**
     * 久未更新
     * @param Order $order
     * @return bool
     * @throws \Exception
     */
    public function computeOrder(Order $order)
    {
        MerchantHelper::setMerchantId($order->merchant_id);

        try {
            $res = $this->basicVariableCompute($order, true);
            return true;
        } catch (\Exception $e) {
            DingHelper::notice(
                json_encode([
                    'file' => $e->getFile() . ":" . $e->getLine(),
                    'order_id' => $order->id,
                    'rule_result' => $res ?? ''
                ]),
                '计算dif_create_install异常 - ' . $e->getMessage() . '-' . app()->environment()
            );
            return false;
        }
    }
}
