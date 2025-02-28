<?php

namespace Api\Controllers\SaasMaster;

use Api\Rules\SaasMaster\MerchantRule;
use Api\Services\SaasMaster\MerchantServer;
use Common\Response\ServicesApiBaseController;

class MerchantController extends ServicesApiBaseController
{
    /**
     * 初始化商户(已创建)
     * @param MerchantRule $rule
     * @return array
     */
    public function initMerchant(MerchantRule $rule)
    {
        if (!$this->validateSign()) {
            return $this->resultFail('验签失败');
        }

        $params = $this->request->post();
        if (!$rule->validate($rule::METHOD_INIT_MERCHANT, $params)) {
            return $this->resultFail($rule->getError());
        }

        $server = MerchantServer::server()->initMerchant($params);

        if (!$server->isSuccess()) {
            return $this->resultFail($server->getMsg());
        }

        return $this->resultSuccess([], $server->getMsg());
    }

    public function createInitMerchant(MerchantRule $rule)
    {
        if (!$this->validateSign()) {
            return $this->resultFail('验签失败');
        }

        $params = $this->request->post();
        if (!$rule->validate($rule::METHOD_CREATE_INIT_MERCHANT, $params)) {
            return $this->resultFail($rule->getError());
        }

        $server = MerchantServer::server()->createInitMerchant($params);

        if (!$server->isSuccess()) {
            return $this->resultFail($server->getMsg());
        }

        return $this->resultSuccess([], $server->getMsg());
    }

    /**
     * 修改商户超级管理员密码
     * @return array
     */
    public function updPassword(MerchantRule $rule)
    {
        if (!$this->validateSign()) {
            return $this->resultFail('验签失败');
        }

        $params = $this->request->post();
        if (!$rule->validate($rule::METHOD_UPD_PASSWORD, $params)) {
            return $this->resultFail($rule->getError());
        }

        $server = MerchantServer::server()->updPassword($this->params['merchantId'], $this->params['adminPassword']);
        if (!$server->isSuccess()) {
            return $this->resultFail($server->getMsg());
        }

        return $this->resultSuccess([], '修改成功');
    }

    /**
     * 获取商户超管账号信息
     * @return array
     */
    public function getSuperAdmin()
    {
        if (!$this->validateSign()) {
            return $this->resultFail('验签失败');
        }

        $server = MerchantServer::server()->getSuperAdmin($this->request->get('merchantIds', 0));
        if (!$server->isSuccess()) {
            return $this->resultFail($server->getMsg());
        }

        return $this->resultSuccess($server->getData(), $server->getMsg());
    }
}
