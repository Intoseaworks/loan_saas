<?php

namespace Risk\Common\Services\SystemApprove\RuleServer;

use Risk\Common\Models\SystemApprove\SystemApproveRule;
use Risk\Common\Services\SystemApprove\RuleExecTrait\SystemApproveRuleTrait;
use Risk\Common\Services\SystemApprove\SystemApproveRuleServer;

class SystemApproveBasicRuleServer extends SystemApproveRuleServer
{
    use SystemApproveRuleTrait;

    protected $module = SystemApproveRule::MODULE_BASIC;
}
