<?php

namespace Common\Jobs\Order;

use Common\Models\Order\ContractAgreement;
use Api\Services\OrderAgreement\OrderAgreementServer;
use Risk\Common\Jobs\Job;

class BuildContractJob extends Job {

    public $tries = 3;
    public $orderId;
//    public $queue = 'order-contract';

    public function __construct($orderId) {
        $this->orderId = $orderId;
    }

    public function handle() {
        echo "重新生成合同:{$this->orderId}";
        OrderAgreementServer::server()->generate($this->orderId, ContractAgreement::CASHNOW_LOAN_CONTRACT, true, false, '', true);
    }

}
