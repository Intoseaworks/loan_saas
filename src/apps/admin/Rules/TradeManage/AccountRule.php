<?php

namespace Admin\Rules\TradeManage;

use Admin\Models\Trade\TradeLog;
use Common\Models\Trade\AdminTradeAccount;
use Common\Rule\Rule;

class AccountRule extends Rule
{
    const SCENARIO_SYSTEM_PAY_LIST = 'system_pay_list';
    const SCENARIO_LIST = 'list';
    const SCENARIO_CREATE = 'create';
    const SCENARIO_CHANGE_STATUS = 'change-status';
    const SCENARIO_CHANGE_DEFAULT = 'change-default';
    const SCENARIO_CHECK = 'check';
    const SCENARIO_OPTION = 'option';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_LIST => [
                'account_no' => 'string',
                'type' => 'in:' . implode(',', array_keys(AdminTradeAccount::TYPE_ALIAS)),
            ],
            self::SCENARIO_SYSTEM_PAY_LIST => [
                'trade_result_time' => 'array',
                'keyword_user' => 'string',
                'channel_code' => 'string|unique:channel,channel_code',
                'trade_result' => 'array',
                'trade_result.*' => 'in:' . implode(',', array_keys(TradeLog::TRADE_RESULT)),
            ],
            self::SCENARIO_CREATE => [
                'type' => 'required|in:' . implode(',', array_keys(AdminTradeAccount::TYPE_ALIAS)),
                'payment_method' => 'required|in:' . implode(',', array_keys(AdminTradeAccount::PLATFORM_ALIAS)),
                'account_no' => 'required',
                'account_name' => 'required|string|min:2',
                'status' => 'required|in:' . implode(',', array_keys(AdminTradeAccount::STATUS_ALIAS)),
            ],
            self::SCENARIO_CHECK => [
                'type' => 'required|in:' . implode(',', array_keys(AdminTradeAccount::TYPE_ALIAS)),
                'payment_method' => 'required|in:' . implode(',', array_keys(AdminTradeAccount::PLATFORM_ALIAS)),
                'account_no' => 'required',
            ],
            self::SCENARIO_CHANGE_STATUS => [
                'id' => 'required|exists:admin_trade_account,id',
                'status' => 'required|in:' . implode(',', array_keys(AdminTradeAccount::STATUS_ALIAS)),
            ],
            self::SCENARIO_CHANGE_DEFAULT => [
                'id' => 'required|exists:admin_trade_account,id',
            ],
            self::SCENARIO_OPTION => [
                'type' => 'in:' . implode(',', array_keys(AdminTradeAccount::TYPE_ALIAS)),
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
            self::SCENARIO_CREATE => [
            ]
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
