<?php

namespace Risk\Common\Services\SystemApprove;

use Common\Services\BaseService;
use Illuminate\Support\Str;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Models\SystemApprove\SystemApproveRule;
use Risk\Common\Services\SystemApprove\RuleData\RuleDataInterface;

/**
 * Class SystemApproveRuleServer
 * @package Common\Services\SystemApprove
 * @property RuleDataInterface $ruleData
 */
class SystemApproveRuleServer extends BaseService
{
    protected $rule;

    protected $order;
    protected $user;

    protected $module;
    /**
     * @var RuleDataInterface
     */
    protected $ruleData;

    protected $currentRule;

    protected $ruleValue = [];

    public function __construct(Order $order, RuleDataInterface $ruleData)
    {
        $this->order = $order;
        $this->user = $order->user;
        $this->rule = $this->getInitRule();
        $this->ruleData = $ruleData;
    }

    protected function getInitRule()
    {
        $appId = $this->order->app_id;
        $userQuality = SystemApproveRule::USER_QUALITY_RELATE[$this->order->quality];

        // 从常量获取用户类型对应的 规则列表
        $allowedRules = SystemApproveRule::getAllRuleByQuality($userQuality);

        if (!isset($this->module)) {
            throw new \Exception('rule server module cannot be null!');
        }

        $rules = SystemApproveRule::model()
            ->getRule($appId, $userQuality, true)
            ->where('module', $this->module)
            ->whereIn('rule', $allowedRules)
            ->toArray();

        return $rules;
    }

    public function getModule()
    {
        return $this->module;
    }

    /**
     * @return SystemApproveRuleServer
     * @throws \Exception
     */
    public function passes()
    {
        $pass = true;
        $rejectRecord = [];
        $record = [];
        foreach ($this->rule as $item) {
            $isClosed = $item['status'] == SystemApproveRule::STATUS_CLOSE;

            $rule = $item['rule'];
            $value = $item['value'];
            // 结果为true表示命中拒绝规则
            $res = $this->check($rule, $value);

            $ruleValue = $this->getRuleValue($rule);
            $ruleValue = is_array($ruleValue) ? json_encode($ruleValue) : $ruleValue;
            // 记录命中的规则
            if ($res && !$isClosed) {
                $pass = false;
                $rejectRecord[$rule] = [
                    'rule' => $rule,
                    'value' => $value,
                    'hit_value' => $ruleValue,
                ];
            }

            // 记录全部规则
            $record[$rule] = [
                'rule' => $rule,
                'value' => $value,
                'exe_value' => $ruleValue,
                'is_closed' => $isClosed,
            ];
        }

        $resRecord = [
            'rejectRecord' => $rejectRecord,
            'record' => $record,
        ];

        if ($pass === false) {
            return $this->outputError('规则未通过', $resRecord);
        }

        return $this->outputSuccess('规则通过', $resRecord);
    }

    protected function check($rule, $params = [])
    {
        $this->setCurrentRule($rule);

        $rule = Str::studly(Str::lower($rule));

        if (!method_exists($this, $replacer = "rule{$rule}")) {
            throw new \Exception("system approve rule method not found!:{$replacer}");
        }

        $res = $this->$replacer(...$params);

        return $res;
    }

    protected function setCurrentRule($rule)
    {
        $this->currentRule = $rule;
    }

    protected function getRuleValue($rule)
    {
        if (isset($this->ruleValue[$rule])) {
            return $this->ruleValue[$rule];
        }
        return null;
    }

    public function getRule()
    {
        return $this->rule;
    }

    public function ruleIsEmpty()
    {
        return empty($this->rule);
    }

    protected function addCurrentRuleValue($value)
    {
        if (isset($this->currentRule)) {
            $this->ruleValue[$this->currentRule] = $value;
        }
    }
}
