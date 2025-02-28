<?php

namespace Api\Controllers\Common;

use Common\Libraries\PayChannel\Fawry\RepayHelper;
use Common\Response\ApiBaseController;

class FawryCallbackController extends ApiBaseController
{
    const MERCHANTID_ID = 1;
    
    public function repay(){
        $params = $this->getParams();
        $res = RepayHelper::helper()->callback($params);
        return $this->resultSuccess($res);
    }

}
