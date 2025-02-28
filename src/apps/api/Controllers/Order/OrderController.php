<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Api\Controllers\Order;

use Api\Models\Order\Order;
use Api\Models\User\User;
use Api\Rules\Order\OrderRule;
use Api\Services\Order\OrderServer;
use Api\Services\Third\WhatsappServer;
use Common\Response\ApiBaseController;
use Common\Services\Config\LoanMultipleConfigServer;
use Common\Services\OrderAgreement\OrderAgreementServer;

class OrderController extends ApiBaseController
{
    /**
     * 订单列表
     * @param OrderRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(OrderRule $rule)
    {
        $user = $this->identity();
        $params = $this->request->all();
        if (!$rule->validate(OrderRule::SCENARIO_INDEX, $params)) {
            return $this->resultFail($rule->getError());
        }
        $data = OrderServer::server()->index($user, $params);
        return $this->resultSuccess($data);
    }

    /**
     * 订单详情
     * @param OrderRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function detail(OrderRule $rule)
    {
        $user = $this->identity();
        $params = $this->request->all();
        if (!$rule->validate(OrderRule::SCENARIO_DETAIL, $params)) {
            return $this->resultFail($rule->getError());
        }
        $data = OrderServer::server()->detail($user, $params);
        # 由于App处理问题需要变更为整数
        $data->renewal_fee = ceil($data->renewal_fee);
        return $this->resultSuccess($data);
    }

    /**
     * 创建订单
     * @param OrderRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(OrderRule $rule)
    {
        $user = $this->identity();
        $params = $this->request->all();
        if (!$rule->validate(OrderRule::SCENARIO_CREATE, $params)) {
            return $this->resultFail($rule->getError());
        }
        if($this->appVersion){
            $params['app_version'] = $this->appVersion;
        }
        $data = OrderServer::server()->create($user, $params);
        //WhatsappServer::server()->checkUserAndContact($user);
        return $this->resultSuccess($data);
    }

    /**
     * 补充资料重新提交
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function replenish()
    {
        $user = $this->identity();
        return $this->resultSuccess(OrderServer::server()->replenish($user));
    }

    /**
     * 订单更新
     * @param OrderRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(OrderRule $rule)
    {
        return $this->resultSuccess('订单更新成功');
        $user = $this->identity();
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_UPDATE, $params)) {
            return $this->resultFail($rule->getError());
        }
        $principal = array_get($params, 'principal');
        $loanDays = array_get($params, 'loan_days');
        $server = OrderServer::server()->update($principal, $loanDays);
        if (!$server->isSuccess()) {
            return $this->resultFail($server->getMsg());
        }
        return $this->resultSuccess($server->getData(), $server->getMsg());
    }

    /**
     * 订单签约(确认订单)
     * @param OrderRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function sign(OrderRule $rule)
    {
        $user = $this->identity();
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_SIGN, $params)) {
            return $this->resultFail($rule->getError());
        }
        $data = OrderServer::server()->sign($user, $params);
        return $this->resultSuccess($data);
    }

    /**
     * 订单取消
     * @param OrderRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function cancel(OrderRule $rule)
    {
        $user = $this->identity();
        $params = $this->getParams();
        if (!$rule->validate(OrderRule::SCENARIO_CANCEL, $params)) {
            return $this->resultFail($rule->getError());
        }
        $data = OrderServer::server()->cancel($user, $params);
        return $this->resultSuccess($data);
    }

    /**
     * 科目计算
     * @param OrderRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function calculate(OrderRule $rule)
    {
        $this->identity();
        $params = $this->getParams();
        if (!$rule->validate(OrderRule::SCENARIO_CALCULATE, $params)) {
            return $this->resultFail($rule->getError());
        }
        $orderId = $this->getParam('order_id');
        $loanDays = $this->getParam('loan_days');
        $principal = $this->getParam('principal');
        return $this->resultSuccess(OrderServer::server()->calculate(Order::model()->getOne($orderId), $loanDays, $principal));
    }

    public function agreement(OrderRule $rule)
    {
        /** @var User $user */
        $user = $this->identity();
        $params = $this->getParams();
        if (!$rule->validate(OrderRule::SCENARIO_AGREEMENT, $params)) {
            return $this->resultFail($rule->getError());
        }
        /** 更新订单额度天数 */
        $principal = $this->getParam('loan_amount');
        $loanDays = $this->getParam('loan_days');
        if(isset($user->order->status) && $user->order->status == Order::STATUS_CREATE){
            $user->order->setScenario(Order::SCENARIO_UPDATE)->saveModel([
                'principal' => $principal,
                'loan_days' => $loanDays,
            ]);
        }
        return $this->resultSuccess(OrderAgreementServer::server()->preview($user->order->id));
    }

    public function config()
    {
        /** @var User $user */
        $user = $this->identity();
        return $this->resultSuccess([
//            'amount' => Config::model()->getLoanAmountRange($user->quality),
//            'days' => Config::model()->getLoanDaysRange($user->quality),
            'amount' => LoanMultipleConfigServer::server()->getLoanAmountRange($user),
            'days' => LoanMultipleConfigServer::server()->getLoanDaysRange($user),
        ]);
    }

    public function repaymentPlan(OrderRule $rule)
    {
        $user = $this->identity();
        $params = $this->request->all();
        if (!$rule->validate(OrderRule::SCENARIO_REPAYMENT_PLAN, $params)) {
            return $this->resultFail($rule->getError());
        }
        $data = OrderServer::server()->orderRepaymentPlan($user, $params);
        return $this->resultSuccess($data);
    }

    /**
     * 分期减免申请repaymentPlan
     *
     * @param OrderRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function orderReduction(OrderRule $rule)
    {
        $user = $this->identity();
        $params = $this->request->all();
        if (!$rule->validate(OrderRule::SCENARIO_REDUCTION, $params)) {
            return $this->resultFail($rule->getError());
        }
        OrderServer::server()->orderReduction($user, $params);
        return $this->resultSuccess();
    }

    public function lastOrder()
    {
        $user = $this->identity();
        $data = OrderServer::server()->getLastOrder($user);
        return $this->resultSuccess($data);
    }

}
