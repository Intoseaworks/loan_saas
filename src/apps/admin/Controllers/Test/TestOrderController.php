<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\Test;

use Admin\Controllers\BaseController;
use Admin\Models\User\User;
use Admin\Services\Test\TestOrderServer;
use Common\Utils\MerchantHelper;

class TestOrderController extends BaseController
{

    /**
     * 订单取消
     *
     * @return array
     */
    public function cancel()
    {
        $params = $this->getParams();

        $merchantId = array_get($params, 'merchant_id');
        if ($merchantId) {
            MerchantHelper::setMerchantId($merchantId);
        }

        TestOrderServer::server()->cancel($params);
        return $this->resultSuccess();
    }

    /**
     * 订单流转
     *
     * @return array
     */
    public function statusUpdate()
    {
        $params = $this->getParams();

        $merchantId = array_get($params, 'merchant_id');
        if ($merchantId) {
            MerchantHelper::setMerchantId($merchantId);
        }

        TestOrderServer::server()->statusUpdate($params);
        return $this->resultSuccess();
    }

    public function statusCreate()
    {
        $params = $this->getParams();

        $merchantId = array_get($params, 'merchant_id');
        if ($merchantId) {
            MerchantHelper::setMerchantId($merchantId);
        }

        TestOrderServer::server()->statusCreate($params);
        return $this->resultSuccess();
    }

    /**
     * 订单逾期
     *
     * @return array
     */
    public function overdue()
    {
        $params = $this->getParams();
        $telephone = $params['telephone'];
        $days = $params['days'];

        $merchantId = array_get($params, 'merchant_id');
        if ($merchantId) {
            MerchantHelper::setMerchantId($merchantId);
        }

        /** @var User $user */
        $user = TestOrderServer::server()->getUser($telephone);
        if (!$user) {
            return $this->resultFail('用户不存在');
        }
        $data = TestOrderServer::server()->overdue($user, $days);
        return $this->resultSuccess([], "更新成功");
    }

}
