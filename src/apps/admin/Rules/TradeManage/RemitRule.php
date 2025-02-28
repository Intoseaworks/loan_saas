<?php

namespace Admin\Rules\TradeManage;

use Admin\Models\Trade\TradeLog;
use Common\Rule\Rule;

class RemitRule extends Rule
{
    /**
     * 验证场景 人工出款列表
     */
    const SCENARIO_MANUAL_REMIT_LIST = 'manual_remit_list';
    /**
     * 验证场景 人工出款提交结果
     */
    const SCENARIO_MANUAL_REMIT_SUBMIT = 'manual_remit_submit';

    /**
     * 验证场景 人工出款失败列表
     */
    const SCENARIO_FAIL_LIST = 'fail_list';
    const SCENARIO_CANCEL_BATCH = 'cancel_batch';
    const SCENARIO_TO_WAIT_REMIT_BATCH = 'to_wait_remit';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_MANUAL_REMIT_LIST => [
            ],
            self::SCENARIO_MANUAL_REMIT_SUBMIT => [
                'order_id' => 'required',
                'pay_platform' => 'required|in:' . implode(',', array_keys(TradeLog::TRADE_PLATFORM)),
//                'trade_result' => 'required|in:' . implode(',', array_keys(TradeLog::TRADE_RESULT)),
                'remark' => 'string',
            ],
            self::SCENARIO_CANCEL_BATCH => [
                'ids' => 'required|array'
            ],
            self::SCENARIO_FAIL_LIST => [
                'trade_platform' => 'in:' . implode(',', array_keys(TradeLog::TRADE_PLATFORM)),
            ],
            self::SCENARIO_TO_WAIT_REMIT_BATCH => [
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
