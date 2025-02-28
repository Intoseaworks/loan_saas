<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/21
 * Time: 11:24
 */

namespace Api\Services\Third;

use Common\Models\Third\ThirdPartyVerify;
use Common\Services\BaseService;

class ThirdPartyVerifyServer extends BaseService
{
    /**
     * @param $panCard
     * @return ThirdPartyVerifyServer
     */
    public function verifyByHistory($cardNo, $type)
    {
        $verifyRes = ThirdPartyVerify::model()->verifyRes($cardNo, $type);
        if (!$verifyRes) {
            return $this->outputError();
        }
        return $this->outputSuccess('success', $verifyRes);
    }
}
