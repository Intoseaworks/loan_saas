<?php

namespace Common\Jobs;

use Api\Services\OrderAgreement\OrderAgreementServer;
use Common\Models\Order\ContractAgreement;
use Common\Models\Order\OrderSignDoc;
use Common\Utils\AadhaarApi\Api\EsignRequest;
use Common\Utils\Email\EmailHelper;

/**
 * Class ContractByEsignPdfJob
 * 拉取esign签名后的合同
 */
class ContractByEsignPdfJob extends Job
{
    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 3;

    public $orderSignDocId;

    public function __construct($orderSignDocId)
    {
        $this->orderSignDocId = $orderSignDocId;
    }

    /**
     * @throws \Throwable
     */
    public function handle()
    {
        try {
            /** @var OrderSignDoc $orderSignDoc */
            $orderSignDoc = OrderSignDoc::query()->find($this->orderSignDocId);
            $order = $orderSignDoc->order;

            if (!$orderSignDoc) {
                $this->_sendErrorEmail([], 'orderSignDoc不存在');
            }
            if (!$order) {
                $this->_sendErrorEmail([], 'order订单不存在');
            }

            if ($orderSignDoc->doc_url) {
                $ossPath = EsignRequest::server()->downloadContractPdf($orderSignDoc->doc_url);
            } else {
                $ossPath = EsignRequest::server()->downloadContractPdfByDocId($orderSignDoc->doc_id);
            }

            $contract = OrderAgreementServer::server()->store($order->id, $ossPath, ContractAgreement::CASHNOW_LOAN_CONTRACT);
            $orderSignDoc->updateContractRelated($contract->id);
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
            'order_sign_doc_id' => $this->orderSignDocId,
        ];
        EmailHelper::send(array_merge($content, (array)$data), $message . '-india saas esign合同拉取队列报错', 'soliang@dingtalk.com');
    }
}
