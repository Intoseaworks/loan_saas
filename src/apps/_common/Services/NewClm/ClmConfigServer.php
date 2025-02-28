<?php

namespace Common\Services\NewClm;

use Common\Models\NewClm\ClmAmount;
use Common\Models\NewClm\ClmInitLevel;
use Common\Models\NewClm\ClmRule;
use Common\Services\BaseService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ClmConfigServer extends BaseService
{
    /**
     * 初始化clm规则配置
     * todo：规则其实在 结清次数 和 逾期天数处 是阶梯递增且不交叉的，所有规则只会命中一条。
     *       但是没想到特别好的方案，暂时单条存储。更新时注意动态调整其他对应的规则阶梯
     *
     * @return bool
     * @throws \Throwable
     */
    public function initRule(): bool
    {
        $typeAdjust = ClmRule::TYPE_ADJUST;
        $typeLimit = ClmRule::TYPE_LIMIT;

        $string = ClmRule::PARAM_TYPE_STRING;
        $range = ClmRule::PARAM_TYPE_RANGE;
        $multiple = ClmRule::PARAM_TYPE_MULTIPLE;

        $optAdd = ClmRule::OPT_ADD;

        $rule = [
            // 预调整规则
            ClmRule::LEVEL_RULE_001 => [$typeAdjust, '首次结清未逾期且上次实际借款金额>=其等级可贷额度', null, $string, $optAdd, 1],
            ClmRule::LEVEL_RULE_002 => [$typeAdjust, '首次结清未逾期且上次实际借款金额<其等级可贷额度', null, $string, $optAdd, 0],
            ClmRule::LEVEL_RULE_003 => [$typeAdjust, '首次结清逾期[1,5]', [1, 5], $range, $optAdd, 0],
            ClmRule::LEVEL_RULE_004 => [$typeAdjust, '首次结清逾期(5,8],本次有卡', [5, 8], $range, $optAdd, 0],
            ClmRule::LEVEL_RULE_005 => [$typeAdjust, '首次结清逾期(5,8],本次无卡', [5, 8], $range, $optAdd, -1],
            ClmRule::LEVEL_RULE_006 => [$typeAdjust, '首次结清逾期8+', 8, $string, $optAdd, -2],
            ClmRule::LEVEL_RULE_007 => [$typeAdjust, '2-3结清未逾期且上次实际借款金额>=其等级可贷额度', [2, 3], $range, $optAdd, 1],
            ClmRule::LEVEL_RULE_008 => [$typeAdjust, '2-3结清未逾期且上次实际借款金额<其等级可贷额度', [2, 3], $range, $optAdd, 0],
            ClmRule::LEVEL_RULE_009 => [$typeAdjust, '2-3结清逾期[1,5]且上次实际借款金额>=其等级可贷额度', [[2, 3], [1, 5]], $multiple, $optAdd, 1],
            ClmRule::LEVEL_RULE_010 => [$typeAdjust, '2-3结清逾期[1,5]且上次实际借款金额<其等级可贷额度', [[2, 3], [1, 5]], $multiple, $optAdd, 0],
            ClmRule::LEVEL_RULE_011 => [$typeAdjust, '2-3结清逾期(5,8],本品牌历史总逾期次数(包括本次)<2', [[2, 3], [5, 8], 2], $multiple, $optAdd, 0],
            ClmRule::LEVEL_RULE_012 => [$typeAdjust, '2-3结清逾期(5,8],本品牌历史总逾期次数(包括本次)>=2', [[2, 3], [5, 8], 2], $multiple, $optAdd, -1],
            ClmRule::LEVEL_RULE_013 => [$typeAdjust, '2-3结清逾期8+', [[2, 3], 8], $multiple, $optAdd, -2],
            ClmRule::LEVEL_RULE_014 => [$typeAdjust, '4次以上结清未逾期且上次实际借款金额>=其等级可贷额度', 4, $string, $optAdd, 2],
            ClmRule::LEVEL_RULE_015 => [$typeAdjust, '4次以上结清未逾期且上次实际借款金额<其等级可贷额度', 4, $string, $optAdd, 0],
            ClmRule::LEVEL_RULE_016 => [$typeAdjust, '4次以上结清且当次结清逾期(0,5]，本品牌历史最大逾期次数(包括本次)<=1 且上次实际借款金额>=其等级可贷额度', [4, [0, 5], 1], $multiple, $optAdd, 1],
            ClmRule::LEVEL_RULE_017 => [$typeAdjust, '4次以上结清且当次结清逾期(0,5]，本品牌历史最大逾期次数(包括本次)<=1 且上次实际借款金额<其等级可贷额度', [4, [0, 5], 1], $multiple, $optAdd, 0],
            ClmRule::LEVEL_RULE_018 => [$typeAdjust, '4次以上结清且当次结清逾期(0,5]，本品牌历史最大逾期次数(包括本次)>1 且上次实际借款金额>=其等级可贷额度', [4, [0, 5], 1], $multiple, $optAdd, 1],
            ClmRule::LEVEL_RULE_019 => [$typeAdjust, '4次以上结清且当次结清逾期(0,5]，本品牌历史最大逾期次数(包括本次)>1 且上次实际借款金额<其等级可贷额度', [4, [0, 5], 1], $multiple, $optAdd, 0],
            ClmRule::LEVEL_RULE_020 => [$typeAdjust, '4次以上结清且当次结清逾期(5,8]，本品牌历史最大逾期次数(包括本次)>1', [4, [5, 8], 1], $multiple, $optAdd, 0],
            ClmRule::LEVEL_RULE_021 => [$typeAdjust, '4次以上结清逾期8+', [4, 8], $multiple, $optAdd, -2],
            // 限定规则
            ClmRule::LEVEL_RULE_022 => [$typeLimit, '无卡限定规则：限定最高等级为4', 4, $string, $optAdd, 0],
            ClmRule::LEVEL_RULE_023 => [$typeLimit, '收入限定规则：等级对应的额度<月收入/2', null, $string, $optAdd, 0],
            ClmRule::LEVEL_RULE_024 => [$typeLimit, '性别限定规则：男性最高等级为12', 12, $string, $optAdd, 0],
            ClmRule::LEVEL_RULE_025 => [$typeLimit, '增额限定规则：本次调整后的额度不超过上次实际借款额度+1000', 1000, $string, $optAdd, 0],
            ClmRule::LEVEL_RULE_026 => [$typeLimit, '全平台逾期最大额度限定：如果用户在全平台曾经最大逾期天数>5,本次调整后的额度<全部逾期天数大于等于5天的合同里最小的借款金额', 5, $string, $optAdd, 0],
        ];

        DB::transaction(function () use ($rule) {
            foreach ($rule as $key => $value) {
                $value[] = ClmRule::STATUS_NORMAL;

                $value = array_map(function ($item) {
                    if (is_array($item)) {
                        return json_encode($item, JSON_UNESCAPED_UNICODE);
                    }
                    return $item;
                }, $value);

                ClmRule::firstOrCreate([
                    'rule' => $key
                ], array_combine([
                    'type',
                    'rule_name',
                    'rule_param',
                    'rule_param_type',
                    'opt',
                    'value',
                    'status',
                ], $value));
            }
        });

        return true;
    }

    /**
     * 初始化clm等级对应金额表
     *
     * @return bool
     * @throws \Throwable
     */
    public function initAmount(): bool
    {
        $initData = [
            [-2, 1000, 0, 'BRONZE'],
            [-1, 1500, 0, 'BRONZE'],
            [0, 2000, 0, 'BRONZE'],
            [1, 2500, 0, 'BRONZE'],
            [2, 3000, 0, 'BRONZE'],
            [3, 3500, 0, 'BRONZE'],
            [4, 4000, 0, 'BRONZE'],
            [5, 4500, 0, 'SILVER'],
            [6, 5000, 5, 'SILVER'],
            [7, 5500, 5, 'SILVER'],
            [8, 6000, 5, 'SILVER'],
            [9, 6500, 5, 'GOLD'],
            [10, 7000, 5, 'GOLD'],
            [11, 7500, 10, 'GOLD'],
            [12, 8000, 10, 'GOLD'],
            [13, 8500, 10, 'GOLD'],
            [14, 9000, 10, 'GOLD'],
            [15, 9500, 10, 'DIAMOND'],
            [16, 10000, 10, 'DIAMOND'],
            [17, 10500, 10, 'DIAMOND'],
            [18, 11000, 10, 'DIAMOND'],
        ];

        DB::transaction(function () use ($initData) {
            foreach ($initData as $item) {
                $insertData = array_combine([
                    'clm_level',
                    'clm_amount',
                    'clm_interest_discount',
                    'alias',
                ], $item);

                ClmAmount::firstOrCreate([
                    'clm_level' => Arr::pull($insertData, 'clm_level'),
                ], $insertData);
            }
        });

        return true;
    }

    /**
     * 初始化等级
     *
     * @param int $merchantId
     *
     * @return bool
     * @throws \Throwable
     */
    public function initLevel(int $merchantId): bool
    {
        $data = [
            ['user_type' => ClmInitLevel::USER_TYPE_WHITELIST, 'payment_type' => ClmInitLevel::PAYMENT_TYPE_BANKCARD, 'clm_level' => 6],
            ['user_type' => ClmInitLevel::USER_TYPE_WHITELIST, 'payment_type' => ClmInitLevel::PAYMENT_TYPE_NON_BANKCARD, 'clm_level' => 2],
            ['user_type' => ClmInitLevel::USER_TYPE_NON_WHITELIST, 'payment_type' => ClmInitLevel::PAYMENT_TYPE_BANKCARD, 'clm_level' => 4],
            ['user_type' => ClmInitLevel::USER_TYPE_NON_WHITELIST, 'payment_type' => ClmInitLevel::PAYMENT_TYPE_NON_BANKCARD, 'clm_level' => 0],
        ];

        DB::transaction(function () use ($merchantId, $data) {
            foreach ($data as $item) {

                ClmInitLevel::firstOrCreate([
                    'merchant_id' => $merchantId,
                    'user_type' => $item['user_type'],
                    'payment_type' => $item['payment_type'],
                ], [
                    'clm_level' => $item['clm_level'],
                ]);
            }
        });

        return true;
    }

    /**
     * 获取clm等级金额配置
     *
     * @return ClmAmount[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getClmAmountConfig()
    {
        $configs = ClmAmount::query()->orderBy('clm_level')->get();

        foreach ($configs as $config) {
            $config->alias = t($config->alias, 'clm');
        }

        return $configs;
    }

    /**
     * 新增等级金额配置
     *
     * @param array $data
     *
     * @return ClmConfigServer
     */
    public function addClmAmountConfig(array $data): self
    {
        $level = $data['level'];

        $maxLevel = ClmAmount::getMaxLevel();
        $minLevel = ClmAmount::getMinLevel();

        if ($level != ($maxLevel + 1) && $level != ($minLevel - 1)) {
            return $this->outputError('新增等级必须与已有等级保持连续');
        }

        $clmAmount = ClmAmount::create([
            'clm_level' => $level,
            'clm_amount' => $data['clm_amount'],
            'clm_interest_discount' => $data['clm_interest_discount'],
            'alias' => $data['alias'],
        ]);

        return $this->outputSuccess('添加成功', $clmAmount);
    }

    /**
     * 编辑等级金额配置
     *
     * @param int $id
     * @param array $data
     *
     * @return $this
     */
    public function editClmAmountConfig(int $id, array $data): self
    {
        $clmAmount = ClmAmount::find($id);

        if (!$clmAmount) {
            return $this->outputError('记录不存在');
        }

        $level = $data['level'];
        if ($level != $clmAmount->clm_level) {
            return $this->outputError('等级不允许编辑');
        }

        $clmAmount->update([
            'clm_amount' => $data['clm_amount'],
            'clm_interest_discount' => $data['clm_interest_discount'],
            'alias' => $data['alias'],
        ]);

        return $this->outputSuccess('编辑成功', $clmAmount->refresh());
    }

    /**
     * 删除等级金额配置
     *
     * @param int $id
     *
     * @return $this
     * @throws \Exception
     */
    public function delClmAmountConfig(int $id): self
    {
        /** @var ClmAmount $clmAmount */
        $clmAmount = ClmAmount::find($id);

        if (!$clmAmount) {
            return $this->outputError('记录不存在');
        }

        $maxLevel = ClmAmount::getMaxLevel();
        $minLevel = ClmAmount::getMinLevel();

        if ($clmAmount->clm_level != $maxLevel && $clmAmount->clm_level != $minLevel) {
            return $this->outputError('只能删除等级最大或最小配置');
        }

        if (ClmInitLevel::existLevel($clmAmount->clm_level)) {
            return $this->outputError('请先移除对应初始化配置');
        }

        $res = $clmAmount->delete();

        return $this->outputSuccess($res);
    }

    /**
     * 获取初始化等级配置
     *
     * @return ClmInitLevel[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getInitLevelConfig()
    {
        $configs = ClmInitLevel::query()->get();

        foreach ($configs as $config) {
            $config->user_type_name = ClmInitLevel::USER_TYPE[$config->user_type] ?? '未知';
            $config->payment_type_name = ClmInitLevel::PAYMENT_TYPE[$config->payment_type] ?? '未知';
        }

        return $configs;
    }

    /**
     * 修改初始化等级配置
     *
     * @param $id
     * @param $level
     *
     * @return ClmConfigServer
     */
    public function editInitLevelConfig($id, $level): self
    {
        $clmInitLevel = ClmInitLevel::find($id);

        if (!$clmInitLevel) {
            return $this->outputError('配置不存在');
        }

        $clmAmount = ClmAmount::getByLevel($level);
        if (!$clmAmount) {
            return $this->outputError('等级不存在');
        }

        $clmInitLevel->update([
            'clm_level' => $level,
        ]);

        return $this->outputSuccess();
    }
}
