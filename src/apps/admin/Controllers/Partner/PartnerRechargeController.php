<?php

namespace Admin\Controllers\Partner;

use Common\Response\AdminBaseController;
use Common\Services\Partner\PartnerRechargeServer;

class PartnerRechargeController extends AdminBaseController
{
    /**
     * 充值记录
     */
    public function rechargeList()
    {
        $params = $this->request->all();

        $list = PartnerRechargeServer::server()->rechargeList($params);
        return $list->getCode() == 18000 ?  $this->resultSuccess($list->getData(), $list->getMsg())
            : $this->resultFail($list->getMsg(), $list->getData());
    }

    /**
     * 充值申请
     */
    public function rechargeApply()
    {
        $params = $this->request->all();

//        //todo 测试
//        $params = [
//            'recharge_amount' => 1000.00,
//            'recharge voucher' => [213, 214],
//        ];

        $apply = PartnerRechargeServer::server()->rechargeApply($params);
        return $apply->getCode() == 18000 ?  $this->resultSuccess($apply->getData(), $apply->getMsg())
            : $this->resultFail($apply->getMsg(), $apply->getData());
    }

}
