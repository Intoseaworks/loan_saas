<?php

namespace Risk\Common\Services\SystemApprove\RuleServer;

use Risk\Common\Models\SystemApprove\SystemApproveRule;
use Risk\Common\Services\SystemApprove\RuleData\RuleDataInterface;
use Risk\Common\Services\SystemApprove\SystemApproveRuleInitServer;

/**
 * Class SystemApproveRuleServer
 * @package Common\Services\SystemApprove
 * @property RuleDataInterface $ruleData
 */
class SystemApproveSandboxRuleServer extends SystemApproveBasicRuleServer {

    /** 需要沙盒测试规则列表 */
    const SANDBOX_RULES = [
//        SystemApproveRule::RULE_APPLY_HIT_KB_AGE_OUT,
//        SystemApproveRule::RULE_APPLY_IP_NOT_IN_INDIA,
//        SystemApproveRule::RULE_APPLY_TELEPHONE_INITIAL_IN_LIST,
//        SystemApproveRule::RULE_DEVICE_UNIQUE_NUMBER_CHANGE,
//        SystemApproveRule::RULE_APPLY_TIME_RANGE_MARRIAGE_EDUCATION_GENDER_MULTIPLE,
//        SystemApproveRule::RULE_CONTACTS_HIT_COMMON_TELEPHONE_CNT,
//        SystemApproveRule::RULE_APPLY_DISTINCT_DEVICE_COUNT,
//        SystemApproveRule::RULE_APPLY_IN_BLACKLIST,
//        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0001,
//        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0002,
//        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0003,
//        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0004,
//        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0005,
//        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0006,
//        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0007,
//        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0008,
//        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0009,
//        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0010,
//        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0011,
//        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0015,
//        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0016,
//        SystemApproveRule::RULE_APPLY_IN_BLACKLIST,
////        SystemApproveRule::RULE_CHECK_OPEN_ORDER,
//        SystemApproveRule::RULE_WOMEN_LAST_LOAN_OVERDUE_DAYS,
//        SystemApproveRule::RULE_MAN_LAST_LOAN_OVERDUE_DAYS,
//        SystemApproveRule::RULE_WOMEN_HIS_OVERDUE_DAYS,
//        SystemApproveRule::RULE_MAN_HIS_OVERDUE_DAYS,
//        SystemApproveRule::RULE_WOMEN_EDU_HIS_OVERDUE_DAYS,
//        SystemApproveRule::RULE_MAN_EDU_HIS_OVERDUE_DAYS,
        SystemApproveRule::RULE_THIRD_DATA_AIRUDDER,
    ];

    protected function getInitRule()
    {
        $quality = $this->order->quality;
        $initRules = SystemApproveRuleInitServer::server()->getInitData($quality);
        /** 取出需要沙盒模拟的规则 */
        $rules = array_only($initRules, self::SANDBOX_RULES);
        /** 构建SystemApproveRuleServer::passes规则格式 */
        $formatRules = [];
        $statusIsClosed = SystemApproveRuleInitServer::server()->getInitIsCloseStatus($quality);
        foreach ($rules as $key => $value) {
            $rule = new SystemApproveRule();
            $rule->value = json_encode($value, JSON_UNESCAPED_UNICODE);
            $rule->type = SystemApproveRule::TYPE_RELATE_RULE[$key];
            $formatRules[$key]['rule'] = $key;
            $formatRules[$key]['type'] = $rule->type;
            $formatRules[$key]['value'] = $rule->parseRule();
            $formatRules[$key]['status'] = in_array($key, $statusIsClosed) ? SystemApproveRule::STATUS_CLOSE : SystemApproveRule::STATUS_NORMAL;
        }
        return array_values($formatRules);
    }
}
