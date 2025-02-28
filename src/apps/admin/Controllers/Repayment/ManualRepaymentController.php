<?php

namespace Admin\Controllers\Repayment;

use Admin\Rules\Repayment\ManualRepaymentRule;
use Admin\Services\Collection\CollectionRecordServer;
use Admin\Services\Collection\CollectionServer;
use Admin\Services\Order\OrderServer;
use Admin\Services\Repayment\ManualRepaymentServer;
use Admin\Services\TradeManage\AccountServer;
use Common\Response\AdminBaseController;

class ManualRepaymentController extends AdminBaseController
{
    /**
     * 人工还款列表
     * @param ManualRepaymentRule $rule
     * @return array
     */
    public function index(ManualRepaymentRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_LIST, $params)) {
            return $this->resultFail($rule->getError());
        }
        $data = ManualRepaymentServer::server()->list($params);
        if ($data->isError()) {
            return $this->resultFail($data->getMsg());
        }

        return $this->resultSuccess($data->getData());
    }

    /**
     * 人工还款详情
     * @param ManualRepaymentRule $rule
     * @return array
     */
    public function detail(ManualRepaymentRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_DETAIL, $params)) {
            return $this->resultFail($rule->getError());
        }

        $data = ManualRepaymentServer::server()->detail($this->getParam('id'));
        if ($data->isError()) {
            return $this->resultFail($data->getMsg());
        }

        return $this->resultSuccess($data->getData());
    }

    /**
     * 动态计算逾期费用
     * @param ManualRepaymentRule $rule
     * @return array
     */
    public function calcOverdue(ManualRepaymentRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_CALC_OVERDUE, $params)) {
            return $this->resultFail($rule->getError());
        }
        $isPart = (bool)array_get($params, 'is_part', 0);

        $data = ManualRepaymentServer::server()->getOverdueData($params['id'], $params['repay_time'], $params['repay_amount'], $isPart);

        return $this->resultSuccess($data);
    }

    /**
     * 收款账户列表
     * @return array
     */
    public function adminTradeRepayAccountList()
    {
        return $this->resultSuccess(AccountServer::server()->getRepayAccount(true));
    }

    /**
     * 提交还款
     * @param ManualRepaymentRule $rule
     * @return array
     * @throws \Exception
     */
    public function repaySubmit(ManualRepaymentRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_REPAY_SUBMIT, $params)) {
            return $this->resultFail($rule->getError());
        }

        $result = ManualRepaymentServer::server()->newRepaySubmit($params);

        if ($result->isError()) {
            return $this->resultFail($result->getMsg());
        }

        return $this->resultSuccess(null, $result->getMsg());
    }

    /**
     * 添加催收记录
     * @param ManualRepaymentRule $rule
     * 联系结果选项见 /api/collection_config/option_dial_progress?type=self
     * @return array
     */
    public function collectionSubmit(ManualRepaymentRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_COLLECTION_SUBMIT, $params)) {
            return $this->resultFail($rule->getError());
        }
        $orderServer = OrderServer::server(array_pull($params, 'order_id'))->canManualRepayment();
        if ($orderServer->isError()) {
            return $this->resultFail($orderServer->getMsg());
        }

        $result = CollectionServer::server()->collectionSubmit($orderServer->getData(), $params);

        if ($result->isError()) {
            return $this->resultFail($result->getMsg());
        }

        return $this->resultSuccess(null, $result->getMsg());
    }

    /**
     * 催收记录列表
     * @return array
     */
    public function collectionRecordList()
    {
        $orderId = $this->getParam('order_id');

        $order = OrderServer::server($orderId)->getOrder();

        $params = array_merge($this->getParams(), [
            'order_id' => $order->id
        ]);

        return $this->resultSuccess(CollectionRecordServer::server()->getPageList($params));
    }

    /**
     * 允许续期
     * @param ManualRepaymentRule $rule
     * @return array
     */
    public function allowRenewal(ManualRepaymentRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_REPAYMENT_ALLOW_RENEWAL, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }

        return $this->resultSuccess(ManualRepaymentServer::server()->allowRenewal($this->getParam('id')));
    }
}
