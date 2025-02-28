<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/31
 * Time: 15:42
 */

namespace Common\Jobs\Order;

use Common\Jobs\Job;
use Common\Services\Collection\CollectionServer;
use Common\Utils\MerchantHelper;

class RenewalStopCollectionJob extends Job {

    public $queue = 'order_deduction';
    public $tries = 3;
    public $orderId;
    public $merchantId;

    public function __construct($orderId, $merchantId = 1) {
        $this->orderId = $orderId;
        $this->merchantId = $merchantId;
    }

    public function handle() {
        echo "Delete merchantID:" . $this->merchantId . "  orderId:" . $this->orderId;
        MerchantHelper::helper()->setMerchantId($this->merchantId);
        $res = CollectionServer::server()->stop($this->orderId);
        echo json_encode($res);
    }

}
