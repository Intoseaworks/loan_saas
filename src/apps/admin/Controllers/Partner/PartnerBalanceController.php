<?php

namespace Admin\Controllers\Partner;

use Common\Response\AdminBaseController;
use Common\Services\Partner\PartnerBalanceServer;
use Common\Services\Partner\PartnerRechargeServer;

class PartnerBalanceController extends AdminBaseController
{
    /**
     * 余额信息
     */
    public function balance()
    {
        $list = PartnerBalanceServer::server()->queryBalance();
        return $list->getCode() == 18000 ?  $this->resultSuccess($list->getData(), $list->getMsg())
            : $this->resultFail($list->getMsg(), $list->getData());
    }
}
