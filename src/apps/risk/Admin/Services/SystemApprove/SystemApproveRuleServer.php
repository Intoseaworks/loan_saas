<?php

namespace Risk\Admin\Services\SystemApprove;

use Common\Services\BaseService;
use Common\Utils\MerchantHelper;
use Risk\Common\Models\SystemApprove\SystemApproveRule;

class SystemApproveRuleServer extends BaseService
{
    public function getRuleByClassify($userQuality, $classify = null)
    {
        $data = [];
        switch ($userQuality) {
            case SystemApproveRule::USER_QUALITY_NEW:
                $data = SystemApproveRule::USER_TYPE_NEW_RULE_DEFAULT;
                break;
            case SystemApproveRule::USER_QUALITY_OLD:
                $data = SystemApproveRule::USER_TYPE_OLD_RULE_DEFAULT;
                break;
        }

        $rules = $classify ? array_only($data, $classify) : $data;
        $merchantId = MerchantHelper::getMerchantId();

        $ruleConfig = SystemApproveRule::model()->getRuleToView($merchantId, $userQuality)->keyBy('rule');

        $result = [];
        foreach ($rules as $key => $item) {

            $dataItem = [];
            foreach ($item as $k => $v) {
                $ruleConfigItem = $ruleConfig->get($k);

                if (!isset($ruleConfigItem)) {
                    $dataItem[$k] = [
                        'rule' => $k,
                        'type' => SystemApproveRule::TYPE_RELATE_RULE[$k],
                        'value' => $v,
                        'status' => SystemApproveRule::STATUS_NORMAL,
                    ];
                } else {
                    $dataItem[$k] = [
                        'rule' => $ruleConfigItem->rule,
                        'type' => $ruleConfigItem->type,
                        'value' => ($ruleConfigItem->type != SystemApproveRule::TYPE_STRING) ?
                            json_decode($ruleConfigItem->value, true) : $ruleConfigItem->value,
                        'status' => $ruleConfigItem->status,
                    ];
                }
            }

            $result[$key] = $dataItem;
        }

        return $result;
    }

    public function updateRuleConfig($userType, $rule, $value, $status)
    {
        $merchantId = MerchantHelper::getMerchantId();

        $res = SystemApproveRule::updOrAdd($merchantId, $userType, $rule, $value, $status);

        return $res;
    }
}
