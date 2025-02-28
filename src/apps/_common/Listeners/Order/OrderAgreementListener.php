<?php

namespace Common\Listeners\Order;

use Common\Events\Order\OrderAgreementEvent;
use Common\Models\Order\ContractAgreement;
use Common\Models\Order\Order;
use Common\Services\OrderAgreement\OrderAgreementServer;
use Common\Utils\MerchantHelper;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderAgreementListener implements ShouldQueue
{
    /**
     * @var Order
     */
    protected $order;

    protected $userId;

    protected $adminTradeAccount;

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
     * @param OrderAgreementEvent $event
     * @return bool
     * @throws \Mpdf\MpdfException
     * @throws \Throwable
     */
    public function handle(OrderAgreementEvent $event)
    {
        $orderId = $event->getOrderId();
        if (isset($event->type)) {
            $type = (array)$event->type;
            foreach ($type as $key) {
                $this->getServerInstance($key)->generate($orderId, $key, true, false, $event->signType);
            }
        } else {
            foreach (ContractAgreement::NEED_AUTO_TYPE as $key) {
                $this->getServerInstance($key)->generate($orderId, $key, true, false, $event->signType);
            }
        }
        return true;
    }

    /**
     * @param $type
     * @return \Api\Services\OrderAgreement\OrderAgreementServer|OrderAgreementServer
     * @throws \Exception
     */
    protected function getServerInstance($type)
    {
        switch ($type) {
            case ContractAgreement::CASHNOW_LOAN_CONTRACT:
                return OrderAgreementServer::server();
                break;
            case ContractAgreement::LOAN_AGREEMENT:
            case ContractAgreement::INTERMEDIARY_AGREEMENT:
            case ContractAgreement::RENEWAL_AGREEMENT:
                return OrderAgreementServer::server();
                break;
            default:
                throw new \Exception('协议生成event type传参错误');
        }
    }
}
