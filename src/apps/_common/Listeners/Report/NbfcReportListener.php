<?php

namespace Common\Listeners\Report;

use Common\Events\Report\NbfcReportEvent;
use Common\Models\Order\Order;
use Common\Services\Report\NbfcReportServer;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Contracts\Queue\ShouldQueue;

class NbfcReportListener implements ShouldQueue
{
    public $tries = 3;

    /**
     * Create the event listener.
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * @param NbfcReportEvent $event
     * @throws \Exception
     */
    public function handle(NbfcReportEvent $event)
    {
        MerchantHelper::clearMerchantId();

        $orderId = $event->getOrderId();
        $reportNode = $event->getNode();

        $order = Order::getById($orderId);
        if (!$order) {
            throw new \Exception('未找到订单');
        }
        MerchantHelper::setMerchantId($order->merchant_id);

        NbfcReportServer::server()->handle($order, $reportNode);
    }

    public function failed(NbfcReportEvent $event, \Exception $exception)
    {
        $content = [
            'orderId' => $event->getOrderId(),
            'file' => $exception->getFile() . ':' . $exception->getLine(),
            'msg' => $exception->getMessage(),
        ];
        DingHelper::notice($content, 'NBFC 上报事件抛错', DingHelper::AT_SOLIANG);
    }
}
