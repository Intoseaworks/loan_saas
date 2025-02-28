<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\Test;

use Admin\Controllers\BaseController;
use Admin\Services\Test\TestAuthServer;
use Admin\Services\Test\TestUserServer;
use Common\Utils\MerchantHelper;

class TestAuthController extends BaseController
{
    public function clearOrComplete()
    {
        $params = $this->request->all();

        $merchantId = array_get($params, 'merchant_id');
        if ($merchantId) {
            MerchantHelper::setMerchantId($merchantId);
        }

        TestAuthServer::server()->clearOrComplete($params);
        return $this->resultSuccess();
    }

    public function clearUser()
    {
        $params = $this->request->all();
        $merchantId = array_get($params, 'merchant_id');
        if ($merchantId) {
            MerchantHelper::setMerchantId($merchantId);
        }
        TestUserServer::server()->clearUser($params);
        return $this->resultSuccess();
    }

}
