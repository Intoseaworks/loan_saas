<?php

namespace Admin\Controllers\Approve;

use Admin\Controllers\BaseController;
use Admin\Models\Order\Order;
use Admin\Rules\Approve\ApproveRule;
use Admin\Services\Approve\ApproveServer;
use Common\Utils\LoginHelper;

class ApproveController extends BaseController
{
    /**
     * 待审批列表
     * @param ApproveRule $rule
     * @return array
     */
    /*public function index(ApproveRule $rule)
    {
        $params = $this->getParams();
        $params['status'] = $this->getParam('status', Order::APPROVAL_PENDING_STATUS);
        if (!$rule->validate(ApproveRule::SCENARIO_INDEX, $params)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(ApproveServer::server()->list($params, true));
    }*/

    /**
     * 人工审批列表
     * @return array
     */
    /*public function approveList()
    {
        $adminId = LoginHelper::getAdminId();

        return $this->resultSuccess(ApproveServer::server()->approveList($adminId));
    }*/

    /**
     * 获取人工审批选项列表
     * @return array
     */
    /*public function approveSelectGroup()
    {
        return $this->resultSuccess(ApproveServer::server()->getResultSelectGroup());
    }*/

    /**
     * 操作审批|提交审批
     * @param ApproveRule $rule
     * @return array
     * @throws \Throwable
     */
    /*public function approveSubmit(ApproveRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate(ApproveRule::SCENARIO_APPROVE_SUBMIT, $params)) {
            return $this->resultFail($rule->getError());
        }

        $orderId = $this->getParam('order_id');
        if (!$this->request->has('first')) {
            $approveResult = $this->getParam('approve_result');
            $remark = $this->getParam('remark');
            ApproveServer::server()->approveSubmit($orderId, $approveResult, $remark);
        }

        // 获取下一单
        $nextOrder = ApproveServer::server()->getNextOrder();

        return $this->resultSuccess($nextOrder);
    }*/

    /**
     * 判断订单能否进入审批详情页
     * @param ApproveRule $rule
     * @return array
     */
    /*public function canApprove(ApproveRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate(ApproveRule::SCENARIO_CAN_APPROVE, $params)) {
            return $this->resultFail($rule->getError());
        }

        if (!ApproveServer::server()->canApprove($params['order_id'])) {
            return $this->resultFail('该审批单已失效，请刷新后重试');
        }

        return $this->resultSuccess();
    }*/

    /**
     * 审批详情
     * @param ApproveRule $rule
     * @return array
     */
    /*public function view(ApproveRule $rule)
    {
        if (!$rule->validate(ApproveRule::SCENARIO_DETAIL, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(ApproveServer::server($this->getParam('id'))->view());
    }*/

    /**
     * 审批拒绝订单列表页
     * @param ApproveRule $rule
     * @return array
     */
    /*public function rejectList(ApproveRule $rule)
    {
        $params = $this->getParams();
        $params['status'] = $this->getParam('status', Order::APPROVAL_REJECT_STATUS);
        if (!$rule->validate(ApproveRule::SCENARIO_REJECT_LIST, $params)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(ApproveServer::server()->rejectList($params));
    }*/

    /**
     * 人审被拒原因详情
     * @param ApproveRule $rule
     * @return array
     */
    /*public function rejectReason(ApproveRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate(ApproveRule::SCENARIO_REJECT_REASON, $params)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(ApproveServer::server($this->getParam('id'))->getRejectReasonDetail());
    }*/
}
