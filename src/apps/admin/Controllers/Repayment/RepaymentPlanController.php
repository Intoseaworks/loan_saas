<?php

namespace Admin\Controllers\Repayment;

use Admin\Exports\Repayment\RepaymentPlanExport;
use Admin\Rules\Order\OrderRule;
use Admin\Rules\Order\RepaymentPlanRule;
use Admin\Services\Order\RepaymentPlanServer;
use Common\Response\AdminBaseController;

class RepaymentPlanController extends AdminBaseController
{
    /**
     * 还款计划列表
     * @param OrderRule $rule
     * @return array
     */
    public function index(OrderRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_REPAYMENT_PLAN, $params)) {
            return $this->resultFail($rule->getError());
        }
        // 导出
        if ((bool)array_get($params, 'export')) {
            $params['export_scene'] = RepaymentPlanExport::SCENE_REPAYMENT_LIST;
        }
        return $this->resultSuccess(RepaymentPlanServer::server()->repaymentPlanList($params, '-appointment_paid_time'));
    }

    /**
     * 还款计划详情
     * @param OrderRule $rule
     * @return array
     */
    public function view(RepaymentPlanRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_DETAIL, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(RepaymentPlanServer::server()->view($this->getParam('id')));
    }

    /**
     * 已还款列表
     * @param OrderRule $rule
     * @return array
     */
    public function paidList(OrderRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_REPAYMENT_PLAN, $params)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(RepaymentPlanServer::server()->paidList($params));
    }

    /**
     * 已还款详情
     * @param OrderRule $rule
     * @return array
     */
    public function paidView(OrderRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_DETAIL, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(RepaymentPlanServer::server($this->getParam('id'))->paidView());
    }

    /**
     * 已逾期列表
     * @param OrderRule $rule
     * @return array
     */
    public function overdueList(OrderRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_REPAYMENT_PLAN, $params)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(RepaymentPlanServer::server()->overdueList($params));
    }

    /**
     * 已逾期详情
     * @param OrderRule $rule
     * @return array
     */
    public function overdueView(OrderRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_DETAIL, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(RepaymentPlanServer::server($this->getParam('id'))->overdueView());
    }

    /**
     * 已坏账列表
     * @param OrderRule $rule
     * @return array
     */
    public function badList(OrderRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_REPAYMENT_PLAN, $params)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(RepaymentPlanServer::server()->badList($params));
    }

    /**
     * 已坏账详情
     * @param OrderRule $rule
     * @return array
     */
    public function badView(OrderRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_DETAIL, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(RepaymentPlanServer::server($this->getParam('id'))->overdueView());
    }
}
