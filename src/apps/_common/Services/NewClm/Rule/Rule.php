<?php

namespace Common\Services\NewClm\Rule;

use Common\Models\NewClm\ClmAmount;
use Common\Models\NewClm\ClmChangeLog;
use Common\Models\NewClm\ClmCustomer;
use Common\Models\NewClm\ClmIndexData;
use Common\Models\NewClm\ClmRule;
use Common\Models\Order\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Common\Models\NewClm\ClmSkipLevel;

class Rule
{
    use RuleTrait;

    /**
     * 预调整规则
     * @var ClmRule[]|\Illuminate\Database\Eloquent\Collection
     */
    protected $adjustRule;

    /**
     * 限定规则
     * @var ClmRule[]|\Illuminate\Database\Eloquent\Collection
     */
    protected $limitRule;

    /**
     * @var ClmCustomer|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null
     */
    protected $clmCustomer;

    /**
     * @var RuleData
     */
    protected $ruleData;

    protected $order;

    protected $currentLevel;

    protected $log;
    protected $class = ClmRule::CLASS_DEFAULT;

    public function __construct(ClmCustomer $customer, Order $order)
    {
        $this->order = $order;
        
        $this->class = $this->getClassByUid($order->user_id);

        $this->adjustRule = ClmRule::getAdjustRule($this->class);

        $this->limitRule = ClmRule::getLimitRule($this->class);

        $this->clmCustomer = $customer;

        if (!$this->clmCustomer) {
            throw new \Exception('订单对应客户不存在');
        }

        $this->currentLevel = $this->clmCustomer->getLevel();

        $this->ruleData = new RuleData($this->clmCustomer, $order);
    }
    
    public function getClassByUid($userId) {
        return ClmRule::CLASS_DEFAULT;
        if ($userId) {
            $tailNum = substr($userId, -1, 1);
            if (in_array($tailNum, range(5, 9))) {
                return "RULE_1115";
            }
        }
        return ClmRule::CLASS_DEFAULT;
    }

    /**
     * 核心入口：获取调整后的等级
     *
     * @return mixed
     * @throws \Exception
     */
    public function getAdjustLevel()
    {
        // 预调整规则
        // 命中规则
        $hintRule = $this->hintRule();

        $level = $this->preAdjust($hintRule);

        // 限定规则
        $level = $this->limit($level);

        return ClmAmount::disposeLevel($level);
    }

    /**
     * 匹配规则
     *
     * @return ClmRule|mixed|null
     * @throws \Exception
     */
    protected function hintRule()
    {
        $hintRule = null;

        foreach ($this->adjustRule as $rule) {

            $ruleKey = $rule->rule;
            $params = $rule->rule_param;

            $ruleMethod = Str::studly(Str::lower($ruleKey));

            if (!method_exists($this, $replacer = "rule{$ruleMethod}")) {
                throw new \Exception("clm rule method not found!:{$replacer}");
            }

            $hint = $this->$replacer(...$params);

            // 命中一条即退出
            if ($hint) {
                $hintRule = $rule;
                break;
            }
        }

        return $hintRule;
    }

    /**
     * 计算调整等级
     *
     * @param ClmRule|null $clmRule
     *
     * @return int
     * @throws \Exception
     */
    protected function preAdjust(?ClmRule $clmRule): int
    {
        $level = $currentLevel = $this->currentLevel;

        if ($clmRule) {
            switch ($clmRule->opt) {
                case ClmRule::OPT_ADD:
                    $level = $level + $clmRule->value;
                    break;
                default:
                    throw new \Exception('clm rule unknown operator');
            }
            # 20211209 Jerry 关闭跳级
            /*
            $skipLevel = ClmSkipLevel::model()->where("user_id", $this->order->user_id)->where("status", 1)->first();
            if ($skipLevel) {
                if ($clmRule->value >= 0) {
                    $level = $skipLevel->new_level;
                    $skipLevel->status = 2;
                } else {
                    $skipLevel->status = 0;
                }
                $skipLevel->save();
            }
            */
            $this->addLog($clmRule->rule, $currentLevel, $level);
        }

        return $level;
    }

    /**
     * 限定规则
     *
     * @param int $level
     *
     * @return int
     * @throws \Exception
     */
    protected function limit(int $level): int
    {
        $levelCache = $level;

        foreach ($this->limitRule as $rule) {

            $ruleKey = $rule->rule;
            $params = $rule->rule_param;

            $ruleMethod = Str::studly(Str::lower($ruleKey));

            if (!method_exists($this, $replacer = "limit{$ruleMethod}")) {
                throw new \Exception("clm limit rule method not found!:{$replacer}");
            }

            $level = $this->$replacer($level, ...$params);

            if ($levelCache != $level) {
                $this->addLog($rule->rule, $levelCache, $level);
            }
            $levelCache = $level;
        }

        return $level;
    }

    /**
     * 新增调整log
     *
     * @param $rule
     * @param $currentLevel
     * @param $adjustLevel
     */
    protected function addLog($rule, $currentLevel, $adjustLevel)
    {
        $this->log[] = [
            'rule' => $rule,
            'old_level' => $currentLevel,
            'value' => $adjustLevel - $currentLevel,
            'class_name' => $this->class ?? ""
        ];
    }

    /**
     * 调整记录入库
     *
     * @return bool
     * @throws \Throwable
     */
    public function logStorage()
    {
        $log = $this->log;

        if (!$log) {
            return true;
        }

        DB::transaction(function () use ($log) {
            foreach ($log as $item) {
                $data = [
                    'clm_customer_id' => $this->clmCustomer->clm_customer_id,
                    'applyid' => $this->order->id,
                    'index_rule' => $item['rule'],
                    'index_value' => $item['value'],
                    'old_level' => $item['old_level'],
                    "class_name" => $item['class_name']
                ];

                ClmIndexData::create($data);
            }
        });

        return true;
    }

    /**
     * 添加等级变更记录
     *
     * @param $toLevel
     *
     * @return mixed
     */
    public function addChangeLog($toLevel, $merchantId = 0)
    {
        $data = [
            'merchant_id' => $merchantId,
            'clm_customer_id' => $this->clmCustomer->clm_customer_id,
            'applyid' => $this->order->id,
            'old_level' => $this->currentLevel,
            'new_level' => $toLevel,
        ];

        return ClmChangeLog::create($data);
    }

    /**
     * 获取log
     *
     * @return mixed
     */
    public function getLog()
    {
        return $this->log;
    }
}
