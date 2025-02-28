<?php

namespace Api\Controllers\Order;

use Api\Rules\Contract\ContractRule;
use Api\Services\Order\OrderServer;
use Api\Services\OrderAgreement\OrderAgreementServer;
use Common\Models\Order\ContractAgreement;
use Common\Services\OrderAgreement\SanctionLetterServer;
use Common\Models\Order\Order;
use Common\Models\Order\OrderSignDoc;
use Common\Response\ServicesApiBaseController;
use Common\Utils\AadhaarApi\Api\EsignRequest;

class ContractController extends ServicesApiBaseController
{
    /**
     * 获取签名认证方digio的链接
     * @param ContractRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Common\Exceptions\RuleException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getSignUrl(ContractRule $rule)
    {
        $this->identity();
        $params = $rule->validateE($rule::SCENARIO_GET_SIGN_URL);

        $signType = OrderSignDoc::TYPE_ESIGN;

        $server = OrderAgreementServer::server()->getOrderSignUrl($signType);
        if (!$server->isSuccess()) {
            return $this->resultFail($server->getMsg());
        }
        return $this->resultSuccess($server->getData(), $server->getMsg());
    }

    /**
     * 生成电子合同(暂弃用)
     * @param ContractRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Common\Exceptions\RuleException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function generateContract(ContractRule $rule)
    {
        $user = $this->identity();
        $params = $rule->validateE($rule::SCENARIO_GENERATE_CONTRACT);

        $order = Order::model()->getOneByUser($params['orderId'], $user->id);
        if (!$order) {
            return $this->resultFail(t('订单不存在'));
        }

        $server = OrderAgreementServer::server()->generateContract($order, $user, false);
        if (!$server->isSuccess()) {
            return $this->resultFail($server->getMsg());
        }
        return $this->resultSuccess($server->getData(), $server->getMsg());
    }

    /**
     * 合同发送指定email
     * @param ContractRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Common\Exceptions\RuleException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function sendContract(ContractRule $rule)
    {
        $this->identity();
        $params = $rule->validateE($rule::SCENARIO_SEND_CONTRACT);
        $order = Order::model()->getOne($params['orderId']);
        if (!$order) {
            return $this->resultFail(t('订单不存在'));
        }
        OrderAgreementServer::server()->sendContract($order, $params['email']);
        return $this->resultSuccess([], t('合同发送成功'));
    }

    /**
     * 获取合同html串
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Mpdf\MpdfException
     * @throws \Throwable
     */
    public function getContract()
    {
        $user = $this->identity();
        $orderId = $this->getParam('orderId');

        if (!$orderId) {
            $orderId = $user->order->id;
        }
        return $this->resultSuccess([
            'telephone' => $user->telephone,
            'html_text' => OrderAgreementServer::server()->generate($orderId, ContractAgreement::CASHNOW_LOAN_CONTRACT)
        ]);
    }
    
    public function getSanctionContract(){
        $user = $this->identity();
        $orderId = $this->getParam('orderId');

        if (!$orderId) {
            $orderId = $user->order->id;
        }
        echo SanctionLetterServer::server()->generate($orderId, true);exit();
    }

    /**
     * 获取订单合同信息
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function contractData()
    {
        $user = $this->identity();
        $orderId = $this->getParam('orderId');

        if ($orderId) {
            $order = Order::model()->getOne($orderId);
        } else {
            $order = $user->order;
        }
        if (!$order) {
            return $this->resultFail(t('订单不存在'));
        }
        $data = OrderAgreementServer::server()->buildData($order);
//        if (array_get($data, 'sign_image_url', '') == '') {
//            return $this->resultFail('Contract acquisition overtime, please retriev');
//        }

        return $this->resultSuccess($data);
    }

    /**
     * esign签约页面 (弃用)
     * @return false|mixed|string
     */
    public function signPage()
    {
        $transactionId = $this->getParam('transactionId');
        if (!$transactionId) {
            return t('订单不存在');
        }
        $html = EsignRequest::server()->buildSignPage($transactionId);
        return $html;
    }

    /**
     * 弃用
     * @return array
     */
    public function signSubmit()
    {
        $server = OrderAgreementServer::server()->signSubmit();
        return $this->resultSuccess($server->getData(), $server->getMsg());
    }

    /**
     *
     * @param ContractRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Common\Exceptions\RuleException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function confirmOpt(ContractRule $rule)
    {
        $user = $this->identity();
        $rule->setTelephone($user->telephone);
        $rule->validateE($rule::SCENARIO_CONFIRM_OTP);

        $order = $user->order;
        if (!$order) {
            return $this->resultFail(t('订单不存在'));
        }
        $server = OrderAgreementServer::server()->generateContract($order, $user);
        if (!$server->isSuccess()) {
            return $this->resultFail($server->getMsg());
        }

        $server = OrderServer::server()->sign($user);
        if (!$server->isSuccess()) {
            return $this->resultFail($server->getMsg());
        }

        return $this->resultSuccess($server->getData(), $server->getMsg());
    }
}
