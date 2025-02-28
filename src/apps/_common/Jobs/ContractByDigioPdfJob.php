<?php

namespace Common\Jobs;

use Api\Services\OrderAgreement\OrderAgreementServer;
use Common\Models\Order\ContractAgreement;
use Common\Models\Order\OrderSignDoc;
use Common\Services\Didio\DigioServer;
use Common\Utils\Email\EmailHelper;

/**
 * Class ContractByDigioPdfJob
 * 拉取digio签名后的合同
 * @package CashNow\Common\Jobs
 */
class ContractByDigioPdfJob extends Job
{
    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 3;

    public $orderDigioId;

    public function __construct($orderDigioId)
    {
        $this->orderDigioId = $orderDigioId;
    }

    /**
     * @throws \Throwable
     */
    public function handle()
    {
        try {
            /** @var OrderSignDoc $orderDigio */
            $orderDigio = OrderSignDoc::query()->find($this->orderDigioId);
            $order = $orderDigio->order;

            if (!$orderDigio) {
                $this->_sendErrorEmail([], 'orderDigio不存在');
            }
            if (!$order) {
                $this->_sendErrorEmail([], 'order订单不存在');
            }
            $ossPath = DigioServer::server()->downloadContractPdf($orderDigio->doc_id);

            $contract = OrderAgreementServer::server()->store($order->id, $ossPath, ContractAgreement::CASHNOW_LOAN_CONTRACT);
            $orderDigio->updateContractRelated($contract->id);
        } catch (\Exception $e) {
            $contractId = isset($contract) ? 'contract_agreement_id:' . $contract->id : '';
            $errorStr = "{$e->getMessage()}\nfile:{$e->getFile()}:{$e->getLine()}\n{$contractId}";
            $this->_sendErrorEmail($errorStr, '队列异常');

            throw $e;
        }
    }

    private function _sendErrorEmail($data, $message = '')
    {
        $content = [
            'order_digio_id' => $this->orderDigioId,
        ];
        EmailHelper::send(array_merge($content, (array)$data), $message . '-cashnow digio 合同拉取队列报错', 'wangshaliang@jiumiaodai.com');
    }
}
