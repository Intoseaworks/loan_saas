<?php

namespace Risk\Common\Listeners\SystemApprove;

use Common\Utils\MerchantHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Risk\Common\Events\SystemApprove\SystemApproveFinishEvent;
use Risk\Common\Helper\LockRedisHelper;
use Risk\Common\Services\SystemApproveData\SystemApproveDataServer;

class ContactLoanAppComparisonListener implements ShouldQueue
{
    public $queue = 'risk-default';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        MerchantHelper::clearMerchantId();
    }

    /**
     * @param SystemApproveFinishEvent $event
     * @return bool
     * @throws \Exception
     */
    public function handle(SystemApproveFinishEvent $event)
    {
        $this->contactLoanAppComparison($event->getAppId(), $event->getOrderId());

        return true;
    }

    /**
     * 通讯录名称比对贷款app入高危电话库
     * @param $appId
     * @param $orderId
     * @return bool
     * @throws \Exception
     */
    public function contactLoanAppComparison($appId, $orderId)
    {
        $key = $appId . '_' . $orderId;
        if ((new LockRedisHelper())->hasRiskContactLoanAppComparison($key)) {
            return false;
        }

        $res = SystemApproveDataServer::server()->contactLoanAppComparison($appId, $orderId);

        if ($res) {
            (new LockRedisHelper)->riskContactLoanAppComparison($key);
        }

        return $res;
    }
}
