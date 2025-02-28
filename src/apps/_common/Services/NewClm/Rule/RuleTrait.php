<?php

namespace Common\Services\NewClm\Rule;

use Common\Models\BankCard\BankCardPeso;
use Common\Models\User\UserInfo;

/**
 * Trait RuleTrait
 *
 * @property RuleData $ruleData
 */
trait RuleTrait
{
    /*************************************************************************************
     * 预调整规则
     ************************************************************************************/

    /**
     * 首次结清 未逾期 且 上次实际借款金额>=其等级可贷额度
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule001(): bool
    {
        // 首次结清 && 未逾期 && 上次实际借款金额>=其等级可贷额度
        return $this->finishNumInRange([0, 1], 2)
            && $this->currentOrderOverdueDaysInRange([-INF, 0])
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsGte();
    }

    /**
     * 首次结清 未逾期 且上次实际借款金额<其等级可贷额度
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule002(): bool
    {
        // 首次结清 && 未逾期 && 上次实际借款金额<其等级可贷额度
        return $this->finishNumInRange([0, 1], 2)
            && $this->currentOrderOverdueDaysInRange([-INF, 0])
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsLt();
    }

    /**
     * 首次结清 逾期[1,5]
     *
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule003(array $params): bool
    {
        // 首次结清 && 逾期[1,5]
        return $this->finishNumInRange([0, 1], 2)
            && $this->currentOrderOverdueDaysInRange($params);
    }

    /**
     * 首次结清逾期(5,8],本次有卡
     *
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule004(array $params): bool
    {
        // 首次结清 && 逾期(5,8] && 本次有卡
        return $this->finishNumInRange([0, 1], 2)
            && $this->currentOrderOverdueDaysInRange($params, 2)
            && $this->currentOrderPaymentIsCard();
    }

    /**
     * 首次结清逾期(5,8],本次无卡
     *
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule005(array $params): bool
    {
        // 首次结清 && 逾期(5,8] && 本次无卡
        return $this->finishNumInRange([0, 1], 2)
            && $this->currentOrderOverdueDaysInRange($params, 2)
            && !$this->currentOrderPaymentIsCard();
    }

    /**
     * 首次结清逾期8+
     *
     * @param $param
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule006($param): bool
    {
        // 首次结清 && 逾期>8
        return $this->finishNumInRange([0, 1], 2)
            && $this->currentOrderOverdueDaysInRange([$param, +INF], 2);
    }

    /**
     * 2-3结清未逾期且上次实际借款金额>=其等级可贷额度
     *
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule007(array $params): bool
    {
        // 第2-3次结清 && 未逾期 && 上次实际借款金额>=其等级可贷额度
        return $this->finishNumInRange($params)
            && $this->currentOrderOverdueDaysInRange([-INF, 0])
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsGte();
    }

    /**
     * 2-3结清未逾期且上次实际借款金额<其等级可贷额度
     *
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule008(array $params): bool
    {
        // 第2-3次结清 && 未逾期 && 上次实际借款金额<其等级可贷额度
        return $this->finishNumInRange($params)
            && $this->currentOrderOverdueDaysInRange([-INF, 0])
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsLt();
    }

    /**
     * 2-3结清逾期[1,5]且上次实际借款金额>=其等级可贷额度
     *
     * @param array $finishRange
     * @param array $overdueRange
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule009(array $finishRange, array $overdueRange): bool
    {
        // 第2-3次结清 && 逾期[1,5] && 上次实际借款金额>=其等级可贷额度
        return $this->finishNumInRange($finishRange)
            && $this->currentOrderOverdueDaysInRange($overdueRange)
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsGte();
    }

    /**
     * 2-3结清逾期[1,5]且上次实际借款金额<其等级可贷额度
     *
     * @param array $finishRange
     * @param array $overdueRange
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule010(array $finishRange, array $overdueRange): bool
    {
        // 第2-3次结清 && 逾期[1,5] && 上次实际借款金额<其等级可贷额度
        return $this->finishNumInRange($finishRange)
            && $this->currentOrderOverdueDaysInRange($overdueRange)
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsLt();
    }

    /**
     * 2-3结清逾期(5,8],本品牌历史总逾期次数(包括本次)<2
     *
     * @param array $finishRange
     * @param array $overdueRange
     * @param $overdueCnt
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule011(array $finishRange, array $overdueRange, $overdueCnt): bool
    {
        // 第2-3次结清 && 逾期(5,8] && 本品牌历史总逾期次数(包括本次)<2
        return $this->finishNumInRange($finishRange)
            && $this->currentOrderOverdueDaysInRange($overdueRange, 2)
            && $this->getHisOverdueCount() < $overdueCnt;
    }

    /**
     * 2-3结清逾期（5,8],本品牌历史总逾期次数(包括本次) >=2
     *
     * @param array $finishRange
     * @param array $overdueRange
     * @param $overdueCnt
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule012(array $finishRange, array $overdueRange, $overdueCnt): bool
    {
        // 第2-3次结清 && 逾期(5,8] && 本品牌历史总逾期次数(包括本次) >=2
        return $this->finishNumInRange($finishRange)
            && $this->currentOrderOverdueDaysInRange($overdueRange, 2)
            && $this->getHisOverdueCount() >= $overdueCnt;
    }

    /**
     * 2-3结清逾期8+
     *
     * @return bool
     */
    protected function ruleLevelRule013(array $finishedRange, $overdueCnt): bool
    {
        // 第2-3次结清 && 逾期>8
        return $this->finishNumInRange($finishedRange)
            && $this->currentOrderOverdueDaysInRange([$overdueCnt, +INF], 2);
    }

    /**
     * 4次以上结清未逾期且上次实际借款金额>=其等级可贷额度
     *
     * @param $param
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule014($param): bool
    {
        // 4次以上结清 && 未逾期 && 上次实际借款金额>=其等级可贷额度
        return $this->finishNumInRange([$param, +INF])
            && $this->currentOrderOverdueDaysInRange([-INF, 0])
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsGte();
    }

    /**
     * 4次以上结清未逾期且上次实际借款金额<其等级可贷额度
     *
     * @param $param
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule015($param): bool
    {
        // 4次以上结清 && 未逾期 && 上次实际借款金额<其等级可贷额度
        return $this->finishNumInRange([$param, +INF])
            && $this->currentOrderOverdueDaysInRange([-INF, 0])
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsLt();
    }

    /**
     * 4次以上结清且当次结清逾期(0,5]，本品牌历史最大逾期天数(包括本次)<=1 且上次实际借款金额>=其等级可贷额度
     *
     * @param $finishedCnt
     * @param array $overdueRange
     * @param $overdueCnt
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule016($finishedCnt, array $overdueRange, $overdueCnt): bool
    {
        // 4次以上结清 && 当次结清逾期(0,5] && 本品牌历史最大逾期天数(包括本次)<=1 && 上次实际借款金额>=其等级可贷额度
        return $this->finishNumInRange([$finishedCnt, +INF])
            && $this->currentOrderOverdueDaysInRange($overdueRange, 2)
            && $this->getHisOverdueCount() <= $overdueCnt
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsGte();
    }

    /**
     * 4次以上结清且当次结清逾期(0,5]，本品牌历史最大逾期天数(包括本次)<=1 且上次实际借款金额<其等级可贷额度
     *
     * @param $finishedCnt
     * @param array $overdueRange
     * @param $overdueCnt
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule017($finishedCnt, array $overdueRange, $overdueCnt): bool
    {
        // 4次以上结清 && 当次结清逾期(0,5] && 本品牌历史最大逾期天数(包括本次)<=1 && 上次实际借款金额<其等级可贷额度
        return $this->finishNumInRange([$finishedCnt, +INF])
            && $this->currentOrderOverdueDaysInRange($overdueRange, 2)
            && $this->getHisOverdueCount() <= $overdueCnt
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsLt();
    }

    /**
     * 4次以上结清且当次结清逾期(0,5]，本品牌历史最大逾期天数(包括本次)>1 且上次实际借款金额>=其等级可贷额度
     *
     * @param $finishedCnt
     * @param array $overdueRange
     * @param $overdueCnt
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule018($finishedCnt, array $overdueRange, $overdueCnt): bool
    {
        // 4次以上结清 && 当次结清逾期(0,5] && 本品牌历史最大逾期天数(包括本次)>1 && 上次实际借款金额>=其等级可贷额度
        return $this->finishNumInRange([$finishedCnt, +INF])
            && $this->currentOrderOverdueDaysInRange($overdueRange, 2)
            && $this->getHisOverdueCount() > $overdueCnt
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsGte();
    }

    /**
     * 4次以上结清且当次结清逾期(0,5]，本品牌历史最大逾期天数(包括本次)>1 且上次实际借款金额<其等级可贷额度
     *
     * @param $finishedCnt
     * @param $overdueRange
     * @param $overdueCnt
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule019($finishedCnt, array $overdueRange, $overdueCnt): bool
    {
        // 4次以上结清 && 当次结清逾期(0,5] && 本品牌历史最大逾期天数(包括本次)>1 && 上次实际借款金额<其等级可贷额度
        return $this->finishNumInRange([$finishedCnt, +INF])
            && $this->currentOrderOverdueDaysInRange($overdueRange, 2)
            && $this->getHisOverdueCount() > $overdueCnt
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsLt();
    }

    /**
     * 4次以上结清且当次结清逾期(5，8]，本品牌历史最大逾期天数(包括本次)>1
     *
     * @param $finishedCnt
     * @param array $overdueRange
     * @param $overdueCnt
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule020($finishedCnt, array $overdueRange, $overdueCnt): bool
    {
        // 4次以上结清 && 当次结清逾期(5,8] && 本品牌历史最大逾期天数(包括本次)>1
        return $this->finishNumInRange([$finishedCnt, +INF])
            && $this->currentOrderOverdueDaysInRange($overdueRange, 2)
            && $this->getHisOverdueCount() > $overdueCnt;
    }

    /**
     * 4次以上结清逾期8+
     *
     * @param $finishedCnt
     * @param $overdueCnt
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule021($finishedCnt, $overdueCnt): bool
    {
        // 4次以上结清 && 当次结清逾期(5,8] && 本品牌历史最大逾期天数(包括本次)>1
        return $this->finishNumInRange([$finishedCnt, +INF])
            && $this->currentOrderOverdueDaysInRange([$overdueCnt, +INF], 2);
    }

    /*************************************************************************************
     * 限定规则
     ************************************************************************************/

    /**
     * 无卡限定规则：限定最高等级为4
     *
     * @param $level
     * @param $limit
     *
     * @return int
     */
    protected function limitLevelRule022($level, $limit): int
    {
        #20211201 Tracy 当前品牌大于15000 完件数大于等于4 跳过规则
        if($this->ruleData->getCurrentLevelAmount()>=239000 && $this->ruleData->getFinishNum() >= 4){
            return $level;
        }
        if (!$this->userPaymentIsCard()) {
            return min($level, $limit);
        }

        return $level;
    }

    /**
     * 收入限定规则：等级对应的额度<月收入/2
     *
     * @param $level
     *
     * @return int
     */
    protected function limitLevelRule023($level): int
    {
        $userSalary = $this->ruleData->getUserSalary();

        $userConfigSalary = $this->ruleData->getUserConfirmSalary();

        // 如果审批时确认了月收入，要以confirm_month_income为准，如果没有就以用户输入的为准
        $limitAmount = $userConfigSalary ?? $userSalary;

        if (is_null($limitAmount)) {
            return $level;
        }

        // 用 月收入/2 去匹配对应等级限制 nio & tracy 20210521
        $limitLevel = $this->ruleData->getLimitLevelByAmount($limitAmount);

        return min($level, $limitLevel);
    }

    /**
     * 性别限定规则：男性最高等级为12
     *
     * @param $level
     * @param $maxLevel
     *
     * @return int
     */
    protected function limitLevelRule024($level, $maxLevel): int
    {
        if ($this->ruleData->isMale()) {
            $level = min($level, $maxLevel);
        }

        return $level;
    }

    /**
     * 增额限定规则：本次调整后的额度不超过上次实际借款额度+1000
     * 由于限制条件都是往下调的，所以这里可以不用最后执行
     *
     * @param $level
     * @param $limitStepAmount
     *
     * @return int
     */
    protected function limitLevelRule025($level, $limitStepAmount): int
    {
        $latelyFinishedLoanAmount = $this->ruleData->getLatelyFinishedLoanAmount();

        // 用 最后一笔完结订单贷款额 + 限制增长额 去匹配对应等级限制
        $limitLevel = $this->ruleData->getLimitLevelByAmount(bcadd($latelyFinishedLoanAmount, $limitStepAmount));

        return min($level, $limitLevel);
    }

    /**
     * 全平台逾期最大额度限定：如果用户在全平台曾经最大逾期天数>5,本次调整后的额度<全部逾期天数大于等于5天的合同里最小的借款金额
     *
     * @param $level
     * @param $param
     *
     * @return int
     */
    protected function limitLevelRule026($level, $param): int
    {
        $minAmount = $this->ruleData->getOverdueDaysExceedMinAmount($param);

        if (isset($minAmount)) {
            $limitLevel = $this->ruleData->getLimitLevelByAmount($minAmount);

            $level = min($level, $limitLevel);
        }

        return $level;
    }

    /*************************************************************************************
     *
     ************************************************************************************/

    /**
     * 判断当前结清次数是否处于指定区间
     *
     * @param array $range
     * @param int $opt
     *
     * @return bool
     * @throws \Exception
     */
    protected function finishNumInRange(array $range, int $opt = 1): bool
    {
        $finishNum = $this->ruleData->getFinishNum();

        return $this->inRange($finishNum, $range, $opt);
    }

    /**
     * 判断当前订单的逾期天数是否处于指定区间
     *
     * @param array $range
     * @param int $opt
     *
     * @return bool
     * @throws \Exception
     */
    protected function currentOrderOverdueDaysInRange(array $range, int $opt = 1): bool
    {
        $currentOrderOverdueDays = $this->ruleData->getCurrentOrderOverdueDays();

        $currentOrderOverdueDays = is_null($currentOrderOverdueDays) ? 0 : $currentOrderOverdueDays;

        return $this->inRange($currentOrderOverdueDays, $range, $opt);
    }
    
    /**
     * 判断当前订单的逾期天数是否处于指定区间
     *
     * @param array $range
     * @param int $opt
     *
     * @return bool
     * @throws \Exception
     */
    protected function currentOrderOverdueDaysInRangeNew(array $range, int $opt = 1): bool
    {
        $currentOrderOverdueDays = $this->ruleData->getCurrentOrderOverdueDays();
        
        $currentOrderOverdueDays = $currentOrderOverdueDays>0 ? $currentOrderOverdueDays : 0;

        $currentOrderOverdueDays = is_null($currentOrderOverdueDays) ? 0 : $currentOrderOverdueDays;

        return $this->inRange($currentOrderOverdueDays, $range, $opt);
    }

    /**
     * 上笔完结订单贷款金额 是否>= 客户当前可贷额度
     *
     * @return bool
     * @throws \Exception
     */
    protected function compareLatelyFinishedLoanAmountAndCurrentLevelIsGte($modificationValue = 0): bool
    {
        return $this->compareLatelyFinishedLoanAmountAndCurrentLevel($modificationValue) !== -1;
    }

    /**
     * 上笔完结订单贷款金额 是否< 客户当前可贷额度
     *
     * @return bool
     * @throws \Exception
     */
    protected function compareLatelyFinishedLoanAmountAndCurrentLevelIsLt($modificationValue = 0): bool
    {
        return $this->compareLatelyFinishedLoanAmountAndCurrentLevel($modificationValue) === -1;
    }

    /**
     * 比较上笔完结订单贷款金额 和 客户当前可贷额度
     *
     * @return int
     * @throws \Exception
     */
    protected function compareLatelyFinishedLoanAmountAndCurrentLevel($modificationValue = 0): int
    {
        $latelyFinishedLoanAmount = $this->ruleData->getLatelyFinishedLoanAmount();

        $currentLevelClmAmount = $this->ruleData->getCurrentLevelAmount() + $modificationValue;

        return bccomp((string)$latelyFinishedLoanAmount, (string)$currentLevelClmAmount);
    }

    /**
     * 判断本次订单是否有卡
     *
     * @return bool
     */
    protected function currentOrderPaymentIsCard()
    {
        return in_array($this->ruleData->getCurrentOrderPaymentType(), [BankCardPeso::PAYMENT_TYPE_BANK, BankCardPeso::PAYMENT_TYPE_OTHER]);
    }

    /**
     * 判断用户是否有卡方式
     *
     * @return bool
     */
    protected function userPaymentIsCard()
    {
        return $this->ruleData->getUserPaymentType() == BankCardPeso::PAYMENT_TYPE_BANK || $this->ruleData->getUserPaymentType() == BankCardPeso::PAYMENT_TYPE_OTHER;
    }

    /**
     * 获取历史逾期次数
     *
     * @return int
     */
    protected function getHisOverdueCount()
    {
        return $this->ruleData->getHisOverdueCount();
    }

    /**
     * 判断是否区间内
     *
     * @param int $value
     * @param array $range
     * @param int $opt
     *
     * @return bool
     * @throws \Exception
     */
    protected function inRange(int $value, array $range, int $opt = 1): bool
    {
        sort($range);

        if (count($range) != 2) {
            throw new \Exception('range incorrect quantity');
        }

        switch ($opt) {
            case 1: // 闭合区间
                $res = $value >= $range[0] && $value <= $range[1];
                break;
            case 2: // 左开右闭
                $res = $value > $range[0] && $value <= $range[1];
                break;
            case 3: // 左闭右开
                $res = $value >= $range[0] && $value < $range[1];
                break;
            case 4: // 开区间
                $res = $value > $range[0] && $value < $range[1];
                break;
            default:
                throw new \Exception('opt arg invalid');
        }

        return $res;
    }
    
    /**
     * 首次结清 未逾期 且 上次实际借款金额>=其等级可贷额度
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule027(): bool
    {
        // 首次结清 && 未逾期 && 上次实际借款金额>=其等级可贷额度
        return $this->finishNumInRange([0, 1], 2)
            && $this->currentOrderOverdueDaysInRange([-INF, 0])
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsGte(-500)
            && $this->currentOrderPaymentIsCard();
    }

    /**
     * 首次结清 未逾期 且上次实际借款金额<其等级可贷额度
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule028(): bool
    {
        // 首次结清 && 未逾期 && 上次实际借款金额>=其等级可贷额度
        return $this->finishNumInRange([0, 1], 2)
            && $this->currentOrderOverdueDaysInRange([-INF, 0])
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsGte(-500)
            && !$this->currentOrderPaymentIsCard();
    }
    
    /**
     * 首次结清 未逾期 且上次实际借款金额<其等级可贷额度
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule029(): bool
    {
        // 首次结清 && 未逾期 && 上次实际借款金额<其等级可贷额度
        return $this->finishNumInRange([0, 1], 2)
            && $this->currentOrderOverdueDaysInRange([-INF, 0])
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsLt(-500);
    }
    
    /**
     * 2-3结清未逾期且上次实际借款金额>=其等级可贷额度-500,本次有卡
     *
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule030(array $params): bool
    {
        // 第2-3次结清 && 未逾期 && 上次实际借款金额>=其等级可贷额度
        return $this->finishNumInRange($params)
            && $this->currentOrderOverdueDaysInRange([-INF, 0])
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsGte(-500)
            && $this->currentOrderPaymentIsCard();
    }
    
    /**
     * 2-3结清未逾期且上次实际借款金额>=其等级可贷额度-500,本次无卡
     *
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule031(array $params): bool
    {
        // 第2-3次结清 && 未逾期 && 上次实际借款金额>=其等级可贷额度
        return $this->finishNumInRange($params)
            && $this->currentOrderOverdueDaysInRange([-INF, 0])
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsGte(-500)
            && !$this->currentOrderPaymentIsCard();
    }
    
    
    /**
     * 2-3结清未逾期且上次实际借款金额<其等级可贷额度-500
     *
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule032(array $params): bool
    {
        return $this->finishNumInRange($params)
            && $this->currentOrderOverdueDaysInRange([-INF, 0])
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsLt(-500);
    }
    
    /**
     * 2-3结清逾期[1,3]且上次实际借款金额>=其等级可贷额度-500,本次有卡
     *
     * @param array $finishRange
     * @param array $overdueRange
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule033(array $finishRange, array $overdueRange): bool
    {
        // 第2-3次结清 && 逾期[1,5] && 上次实际借款金额>=其等级可贷额度
        return $this->finishNumInRange($finishRange)
            && $this->currentOrderOverdueDaysInRange($overdueRange)
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsGte(-500)
            && $this->currentOrderPaymentIsCard();
    }
    
    /**
     * 2-3结清逾期[1,3]且上次实际借款金额>=其等级可贷额度-500,本次无卡
     *
     * @param array $finishRange
     * @param array $overdueRange
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule034(array $finishRange, array $overdueRange): bool
    {
        // 第2-3次结清 && 逾期[1,5] && 上次实际借款金额>=其等级可贷额度
        return $this->finishNumInRange($finishRange)
            && $this->currentOrderOverdueDaysInRange($overdueRange)
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsGte(-500)
            && !$this->currentOrderPaymentIsCard();
    }
    
    /**
     * 2-3结清逾期[1,3]且上次实际借款金额<其等级可贷额度-500
     *
     * @param array $finishRange
     * @param array $overdueRange
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule035(array $finishRange, array $overdueRange): bool
    {
        // 第2-3次结清 && 逾期[1,5] && 上次实际借款金额<其等级可贷额度
        return $this->finishNumInRange($finishRange)
            && $this->currentOrderOverdueDaysInRange($overdueRange)
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsLt(-500);
    }
    
    /**
     * 4次以上结清且当次结清逾期[0,2]且上次实际借款金额>=其等级可贷额度-500，本次有卡
     *
     * @param $finishedCnt
     * @param array $overdueRange
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule036($finishedCnt, array $overdueRange): bool
    {
        return $this->finishNumInRange([$finishedCnt, +INF])
            && $this->currentOrderOverdueDaysInRangeNew($overdueRange, 1)
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsGte(-500)
            && $this->currentOrderPaymentIsCard();
    }
    
    /**
     * 4次以上结清且未逾期且上次实际借款金额>=其等级可贷额度-500，本次无卡
     *
     * @param $finishedCnt
     * @param array $overdueRange
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule037($finishedCnt): bool
    {
        return $this->finishNumInRange([$finishedCnt, +INF])
            && $this->currentOrderOverdueDaysInRange([-INF, 0])
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsGte(-500)
            && !$this->currentOrderPaymentIsCard();
    }
    
    /**
     * 4次以上结清且当次结清逾期[0,2]且上次实际借款金额>=其等级可贷额度-500，本次有卡
     *
     * @param $finishedCnt
     * @param array $overdueRange
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule038($finishedCnt, array $overdueRange): bool
    {
        return $this->finishNumInRange([$finishedCnt, +INF])
            && $this->currentOrderOverdueDaysInRange($overdueRange, 2)
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsGte(-500)
            && !$this->currentOrderPaymentIsCard();
    }
    
    /**
     * 4次以上结清当次结清逾期[0,2]且上次实际借款金额<其等级可贷额度-500，本次有卡
     *
     * @param $param
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule039($param, array $overdueRange): bool
    {
        return $this->finishNumInRange([$param, +INF])
            && $this->currentOrderOverdueDaysInRangeNew($overdueRange, 1)
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsLt(-500)
            && $this->currentOrderPaymentIsCard();
    }
    
    /**
     * 4次以上结清且未逾期且上次实际借款金额<其等级可贷额度-500，本次无卡
     *
     * @param $param
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule040($param): bool
    {
        return $this->finishNumInRange([$param, +INF])
            && $this->currentOrderOverdueDaysInRange([-INF, 0])
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsLt(-500)
            && !$this->currentOrderPaymentIsCard();
    }
    
    /**
     * 4次以上结清当次结清逾期[0,2]且上次实际借款金额<其等级可贷额度-500，本次有卡
     *
     * @param $param
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule041($param, array $overdueRange): bool
    {
        return $this->finishNumInRange([$param, +INF])
            && $this->currentOrderOverdueDaysInRange($overdueRange, 2)
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsLt(-500)
            && !$this->currentOrderPaymentIsCard();
    }
    
    /**
     * 4次以上结清且当次结清逾期(2,5]，本品牌历史逾期次数(不包括本次)<=1 且上次实际借款金额>=其等级可贷额度-500，本次有卡
     *
     * @param $finishedCnt
     * @param array $overdueRange
     * @param $overdueCnt
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule042($finishedCnt, array $overdueRange, $overdueCnt): bool
    {
        return $this->finishNumInRange([$finishedCnt, +INF])
            && $this->currentOrderOverdueDaysInRange($overdueRange, 2)
            && $this->getHisOverdueCount() <= $overdueCnt
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsGte(-500)
            && $this->currentOrderPaymentIsCard();
    }
    
    /**
     * 4次以上结清且当次结清逾期(2,5]，本品牌历史逾期次数(不包括本次)<=1 且上次实际借款金额>=其等级可贷额度-500，本次无卡
     *
     * @param $finishedCnt
     * @param array $overdueRange
     * @param $overdueCnt
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule043($finishedCnt, array $overdueRange, $overdueCnt): bool
    {
        return $this->finishNumInRange([$finishedCnt, +INF])
            && $this->currentOrderOverdueDaysInRange($overdueRange, 2)
            && $this->getHisOverdueCount() <= $overdueCnt
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsGte(-500)
            && !$this->currentOrderPaymentIsCard();
    }
    
    /**
     * 4次以上结清且当次结清逾期(2,5]，本品牌历史逾期次数(不包括本次)<=1 且上次实际借款金额<其等级可贷额度-500
     *
     * @param $finishedCnt
     * @param array $overdueRange
     * @param $overdueCnt
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule044($finishedCnt, array $overdueRange, $overdueCnt): bool
    {
        // 4次以上结清 && 当次结清逾期(0,5] && 本品牌历史最大逾期天数(包括本次)<=1 && 上次实际借款金额<其等级可贷额度
        return $this->finishNumInRange([$finishedCnt, +INF])
            && $this->currentOrderOverdueDaysInRange($overdueRange, 2)
            && $this->getHisOverdueCount() <= $overdueCnt
            && $this->compareLatelyFinishedLoanAmountAndCurrentLevelIsLt(-500);
    }
    
    /**
     * 4次以上结清且当次结清逾期(5,8]
     *
     * @param $finishedCnt
     * @param array $overdueRange
     * @param $overdueCnt
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule045($finishedCnt, array $overdueRange): bool
    {
        return $this->finishNumInRange([$finishedCnt, +INF])
            && $this->currentOrderOverdueDaysInRange($overdueRange, 2);
    }
    
    /**
     * 4次以上结清且当次结清逾期(5,8]，本品牌历史逾期次数(不包括本次)<=1
     *
     * @param $finishedCnt
     * @param array $overdueRange
     * @param $overdueCnt
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule046($finishedCnt, array $overdueRange, $overdueCnt): bool
    {
        // 4次以上结清 && 当次结清逾期(0,5] && 本品牌历史最大逾期天数(包括本次)<=1 && 上次实际借款金额>=其等级可贷额度
        return $this->finishNumInRange([$finishedCnt, +INF])
            && $this->currentOrderOverdueDaysInRange($overdueRange, 2)
            && $this->getHisOverdueCount() <= $overdueCnt;
    }
    
    /**
     * 首次结清逾期[1,3],本次有卡
     *
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule047(array $params): bool
    {
        return $this->finishNumInRange([0, 1], 2)
            && $this->currentOrderOverdueDaysInRange($params, 1)
            && $this->currentOrderPaymentIsCard();
    }
    
    /**
     * 首次结清逾期[1,3],本次无卡
     *
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    protected function ruleLevelRule048(array $params): bool
    {
        return $this->finishNumInRange([0, 1], 2)
            && $this->currentOrderOverdueDaysInRange($params, 1)
            && !$this->currentOrderPaymentIsCard();
    }
}
