<?php

namespace Admin\Controllers\NewApprove;

use Admin\Controllers\BaseController;
use Admin\Models\Order\Order;
use Admin\Rules\Approve\ApproveRule;
use Admin\Services\NewApprove\NewApproveServer;

class NewApproveController extends BaseController
{
    /**
     * 审批拒绝订单列表页
     * @param ApproveRule $rule
     * @return array
     */
    public function rejectList(ApproveRule $rule)
    {
        $params = $this->getParams();
        $params['status'] = $this->getParam('status', Order::APPROVAL_REJECT_STATUS);
        if (!$rule->validate(ApproveRule::SCENARIO_REJECT_LIST, $params)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(NewApproveServer::server()->rejectList($params));
    }

    /**
     * 人审被拒原因详情
     * @param ApproveRule $rule
     * @return array
     */
    public function rejectReason(ApproveRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate(ApproveRule::SCENARIO_REJECT_REASON, $params)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(NewApproveServer::server()->getRejectReasonDetail($this->getParam('id')));
    }
}
