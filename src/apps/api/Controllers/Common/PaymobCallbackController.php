<?php

namespace Api\Controllers\Common;

use Common\Libraries\PayChannel\Paymob\PayoutHelper;
use Common\Response\ApiBaseController;

class PaymobCallbackController extends ApiBaseController
{
    const MERCHANTID_ID = 1;
    
    public function payout(){
        $params = $this->getParams();
        $res = PayoutHelper::helper()->callback($params);
        return $this->resultSuccess($res);
    }

}
