<?php

namespace Risk\Common\Services\SystemApprove;

use Common\Services\BaseService;
use Risk\Common\Models\SystemApprove\SystemApproveRule;

class SystemApproveRuleInitServer extends BaseService
{
    public function initRule($appId)
    {
        foreach (SystemApproveRule::USER_QUALITY as $userQuality => $note) {
            $data = $this->getInitData($userQuality);
            $statusIsClosed = $this->getInitIsCloseStatus($userQuality);

            foreach ($data as $rule => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $status = in_array($rule, $statusIsClosed) ? SystemApproveRule::STATUS_CLOSE : SystemApproveRule::STATUS_NORMAL;

                if (in_array($rule, SystemApproveRule::NEED_FORCED_UPDATING_RULE)) {
                    $this->updateOrCreateRule($appId, $userQuality, $rule, $value, $status);
                } else {
                    $this->firstOrCreateRule($appId, $userQuality, $rule, $value, $status);
                }
            }
        }
        return true;
    }

    public function getInitData($userQuality)
    {
        $data = [];
        switch ($userQuality) {
            case SystemApproveRule::USER_QUALITY_NEW:
                $data = array_collapse(SystemApproveRule::USER_TYPE_NEW_RULE_DEFAULT);
                break;
            case SystemApproveRule::USER_QUALITY_OLD:
                $data = array_collapse(SystemApproveRule::USER_TYPE_OLD_RULE_DEFAULT);
                break;
        }
        return $data;
    }

    public function getInitIsCloseStatus($userQuality)
    {
        $res = [];
        switch ($userQuality) {
            case SystemApproveRule::USER_QUALITY_NEW:
                $res = SystemApproveRule::USER_TYPE_NEW_RULE_DEFAULT_STATUS_IS_CLOSE;
                break;
            case SystemApproveRule::USER_QUALITY_OLD:
                $res = SystemApproveRule::USER_TYPE_OLD_RULE_DEFAULT_STATUS_IS_CLOSE;
                break;
        }
        return $res;
    }

    public function updateOrCreateRule($appId, $userQuality, $rule, $value, $status)
    {
        $model = SystemApproveRule::updateOrCreate([
            'app_id' => $appId,
            'user_quality' => $userQuality,
            'rule' => $rule,
        ], [
            'type' => SystemApproveRule::TYPE_RELATE_RULE[$rule],
            'value' => $value,
            'status' => $status,
            'module' => $this->getModuleByRule($rule),
        ]);

        return $model;
    }

    protected function getModuleByRule($rule)
    {
        switch (true) {
            default:
                return SystemApproveRule::MODULE_BASIC;
        }
    }

    public function firstOrCreateRule($appId, $userQuality, $rule, $value, $status)
    {
        $model = SystemApproveRule::firstOrCreate([
            'app_id' => $appId,
            'user_quality' => $userQuality,
            'rule' => $rule,
        ], [
            'type' => SystemApproveRule::TYPE_RELATE_RULE[$rule],
            'value' => $value,
            'status' => $status,
            'module' => $this->getModuleByRule($rule),
        ]);

        return $model;
    }
}
