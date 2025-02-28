<?php

namespace Admin\Rules\NewClm;

use Common\Models\NewClm\ClmAmount;
use Common\Rule\Rule;

class ClmConfigRule extends Rule
{
    // 新增等级金额配置
    const SCENARIO_ADD_CLM_AMOUNT_CONFIG = 'addClmAmountConfig';
    // 编辑等级金额配置
    const SCENARIO_EDIT_CLM_AMOUNT_CONFIG = 'editClmAmountConfig';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_ADD_CLM_AMOUNT_CONFIG => [
                'level' => 'integer|required',
                'clm_amount' => 'numeric|required|min:' . ClmAmount::MIN_CLM_AMOUNT,
                'clm_interest_discount' => 'numeric|required|min:0|max:100', // 优惠比率，百分比
                'alias' => 'string|required|max:10',
            ],
            self::SCENARIO_EDIT_CLM_AMOUNT_CONFIG => [
                'id' => 'required',
                'level' => 'integer|required',
                'clm_amount' => 'numeric|required|min:' . ClmAmount::MIN_CLM_AMOUNT,
                'clm_interest_discount' => 'numeric|required|min:0|max:100', // 优惠比率，百分比
                'alias' => 'string|required|max:10',
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [];
    }

    public function attributes()
    {
        return [
            'level' => '等级',
            'clm_amount' => '额度上限',
            'clm_interest_discount' => '服务费优惠比率',
            'alias' => '对应前端等级展示',
        ];
    }
}
