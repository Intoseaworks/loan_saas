<?php

namespace Risk\Admin\Controllers\SystemApprove;

use Common\Response\AdminBaseController;
use Risk\Admin\Rules\SystemApprove\SystemApproveConfigRule;
use Risk\Admin\Services\SystemApprove\SystemApproveRuleServer;
use Risk\Common\Models\SystemApprove\SystemApproveRule;

class SystemApproveConfigController extends AdminBaseController
{
    /**
     * 机审规则设置
     * @param SystemApproveConfigRule $rule
     * @return array
     */
    public function systemApproveSave(SystemApproveConfigRule $rule)
    {
        $params = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_SYSTEM_APPROVE_SAVE, $params)) {
            return $this->resultFail($rule->getError());
        }

        $result = SystemApproveRuleServer::server()->updateRuleConfig(
            $params['user_type'],
            $params['rule'],
            array_get($params, 'value'),
            array_get($params, 'status')
        );

        if (!$result) {
            return $this->resultFail('保存失败');
        }

        return $this->resultSuccess([], '保存成功');
    }

    /**
     * 查看机审规则设置
     * @return array
     */
    public function systemApproveView()
    {
        $classify = $this->getParam('classify');
        $userQuality = $this->getParam('user_quality', SystemApproveRule::USER_QUALITY_NEW);

        $result = SystemApproveRuleServer::server()->getRuleByClassify($userQuality, $classify);

        return $this->resultSuccess($result);
    }
}
