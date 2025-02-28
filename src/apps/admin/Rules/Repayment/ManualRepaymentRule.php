<?php

namespace Admin\Rules\Repayment;

use Admin\Models\Collection\Collection;
use Common\Rule\Rule;
use Common\Validators\Validation;
use Illuminate\Support\Facades\Validator;

class ManualRepaymentRule extends Rule
{
    const SCENARIO_LIST = 'list';
    const SCENARIO_DETAIL = 'detail';
    const SCENARIO_CALC_OVERDUE = 'calc_overdue';
    const SCENARIO_REPAY_SUBMIT = 'repay_submit';
    const SCENARIO_COLLECTION_SUBMIT = 'collection_submit';
    const SCENARIO_REPAYMENT_ALLOW_RENEWAL  = 'repayment_allow_renewal'; //允许续期

    /**
     * @return array
     */
    public function rules()
    {
        Validator::extendImplicit('progress_validate', function ($attribute, $value, $parameters, Validation $validator) {
            $dial = array_get($validator->getData(), 'dial');
            return isset(Collection::PROGRESS_SELF[$dial]) && array_key_exists($value, Collection::PROGRESS_SELF[$dial]);
        }, '联系结果选项错误');

        return [
            self::SCENARIO_LIST => [
                'keyword' => 'string',
                'order_ids' => 'array',
                'status' => 'array',
                'appointment_paid_time' => 'array',
            ],
            self::SCENARIO_DETAIL => [
                'id' => 'required',
            ],
            self::SCENARIO_CALC_OVERDUE => [
                'id' => 'required',
                'repay_time' => 'required|date_format:Y-m-d|before_or_equal:' . date('Y-m-d'),
                'repay_amount' => 'required|numeric',
                'is_part' => 'required|in:0,1',
            ],
            self::SCENARIO_REPAY_SUBMIT => [
                'id' => 'required',
                //'trade_account_id' => 'required|exists:admin_trade_account,id,type,' . AdminTradeAccount::TYPE_IN,
                'remark' => 'string',
                'repay_name' => 'required',
                'repay_telephone' => 'required|numeric',
                'repay_account' => 'required',
                'repay_time' => 'required|date_format:Y-m-d H:i:s|before:' . date('Y-m-d H:i:s'),
                'repay_amount' => 'required|numeric',
                'is_part' => 'required|in:0,1',
            ],
            self::SCENARIO_COLLECTION_SUBMIT => [
                'order_id' => 'required',
                'dial' => 'required|in:' . implode(',', array_keys(Collection::DIAL_SELF)),
                'progress' => 'required|progress_validate',
                'promise_paid_time' => 'date_format:Y-m-d',
                'remark' => 'string',
            ],
            /** 允许续期 */
            self::SCENARIO_REPAYMENT_ALLOW_RENEWAL  => [
                'id' => 'required|integer',
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
            self::SCENARIO_CALC_OVERDUE => [
                'repay_time.before_or_equal' => '还款支付时间 不能大于当前日期',
            ],
            self::SCENARIO_REPAY_SUBMIT => [
                'repay_time.before_or_equal' => '还款支付时间 不能大于当前日期',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'repay_time' => '还款支付时间',
            'trade_account_id' => '收款账户'
        ];
    }
}
