<?php

namespace Admin\Controllers\Repayment;

use Admin\Rules\Order\RepaymentPlanRule;
use Admin\Services\Repay\MockRestoreServer;
use Admin\Services\Repay\RepayServer;
use Admin\Services\Repayment\RenewalRepaymentServer;
use Common\Response\AdminBaseController;

/**
 * 调账模块
 * Created by PhpStorm.
 * User: zy
 * Date: 20-11-6
 * Time: 下午3:37
 */
class AdjustmentRepayController extends AdminBaseController
{
    /**
     * 调账管理
     * @param RepaymentPlanRule $rule
     * @return array
     */
    public function index(RepaymentPlanRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_ADJUSTMENT_LIST, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }

        return $this->resultSuccess(RepayServer::server()->repayDetailList($this->getParams()));
    }

    /**
     * 撤销
     * @param RepaymentPlanRule $rule
     * @return array
     */
    public function revoke(RepaymentPlanRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_ADJUSTMENT_CANCEL, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }

        return $this->resultSuccess(RepayServer::server()->revoke($this->getParams()));
    }

    /**
     * 历史调账记录
     * @param RepaymentPlanRule $rule
     * @return array
     */
    public function adjustmentList(RepaymentPlanRule $rule)
    {
        if (!$rule->validate($rule::SCENARIO_ADJUSTMENT_HISTORY, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }

        return $this->resultSuccess(RepayServer::server()->adjustmentList($this->getParams()));
    }

    /**
     * 调账并结清
     * @param RepaymentPlanRule $rule
     * @return array
     */
    public function complete(RepaymentPlanRule $rule) {
        if (!$rule->validate($rule::SCENARIO_ADJUSTMENT_COMPLETE, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }

        return $this->resultSuccess(RepayServer::server()->completeRepaymentPlan($this->getParams()));
    }

    /**
     * 只调账
     * @param RepaymentPlanRule $rule
     * @return array
     */
    public function only(RepaymentPlanRule $rule) {
        if (!$rule->validate($rule::SCENARIO_ADJUSTMENT_ONLY, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }

        return $this->resultSuccess(RepayServer::server()->adjustmentOnly($this->getParams()));
    }

}
