<?php

namespace Api\Controllers\Callback;

use Common\Response\ServicesApiBaseController;
use Api\Services\Pay\DragonPayService;
use Common\Utils\MerchantHelper;

class DragonpayController extends ServicesApiBaseController {

    public function index() {
        MerchantHelper::helper()->setMerchantId(3);
        return DragonPayService::server()->callback($this->getParams());
    }
    public function u4() {
        MerchantHelper::helper()->setMerchantId(2);
        return DragonPayService::server()->callback($this->getParams());
    }
    
    public function c1(){
        MerchantHelper::helper()->setMerchantId(5);
        return DragonPayService::server()->callback($this->getParams());
    }

    public function u3() {
        MerchantHelper::helper()->setMerchantId(6);
        return DragonPayService::server()->callback($this->getParams());
    }

}
