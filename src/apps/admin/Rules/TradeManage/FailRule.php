<?php

namespace Admin\Rules\TradeManage;

use Common\Rule\Rule;

class FailRule extends Rule
{
    /**
     * 验证场景 人工出款列表
     */
    const SCENARIO_FAIL_LIST = 'fail_list';
    const SCENARIO_CANCEL_BATCH = 'cancel_batch';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_FAIL_LIST => [
            ],
            self::SCENARIO_CANCEL_BATCH => [
                'ids' => 'required|array'
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
