<?php

namespace Api\Rules\Repay;

use Common\Models\Trade\TradeLog;
use Common\Rule\Rule;

class RepayRule extends Rule
{
    const SCENARIO_DAIKOU = 'daikou';
    const SCENARIO_QUERY_ORDER = 'query_order';
    const SCENARIO_REPAY = 'repay';
    const SCENARIO_APP_REPAY = 'app_repay';
    const SCENARIO_ADD_REPAY_BANK = 'add_repay_bank';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_DAIKOU => [
                'order_id' => 'required',
            ],
            self::SCENARIO_QUERY_ORDER => [
//                'trade_log_id' => 'required',
            ],
            self::SCENARIO_REPAY => [
                'channel' => 'required|in:' . implode(',', array_merge(TradeLog::TRADE_PLATFORM_HAS_REPAY, [TradeLog::TRADE_PLATFORM_DEDUCTION])),
                'orderId' => 'required',
                'repayAmount' => 'numeric',
            ],
            self::SCENARIO_APP_REPAY => [
                'channel' => 'required|in:' . implode(',', TradeLog::TRADE_PLATFORM_HAS_REPAY),
                'orderId' => 'required',
                'repayAmount' => 'numeric',
            ],
            self::SCENARIO_ADD_REPAY_BANK => [
                "holder_name" => "required",
                "card_number" => "required",
                "expiry_year" => "required|numeric",
                "expiry_month" => "required|numeric",
                "cvv" => "required",
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
            self::SCENARIO_DAIKOU => [

            ],
            self::SCENARIO_REPAY => [
            ],
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
