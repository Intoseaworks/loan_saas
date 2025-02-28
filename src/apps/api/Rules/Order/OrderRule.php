<?php

namespace Api\Rules\Order;

use Common\Rule\Rule;

class OrderRule extends Rule
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_SIGN = 'sign';
    const SCENARIO_CANCEL = 'cancel';
    const SCENARIO_INDEX = 'index';
    const SCENARIO_DETAIL = 'detail';
    const SCENARIO_CALCULATE = 'calculate';
    const SCENARIO_AGREEMENT = 'agreement';
    const SCENARIO_REPAYMENT_PLAN = 'repayment_plan';
    const SCENARIO_REDUCTION = 'reduction';
    const SCENARIO_LAST_ORDER = 'last_order';
    const SCENARIO_TRIAL = 'trial'; //试算

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_CREATE => [
                //'product_id' => 'required|in:1,2,3',
                'client_id' => 'required',
            ],
            self::SCENARIO_UPDATE => [
                'principal' => 'required|numeric',
                'loan_days' => 'required|integer',
            ],
            self::SCENARIO_SIGN => [
                'principal' => 'required|numeric|min:100',
                'loan_days' => 'required|integer',
                'position' => 'required|json',
                'loan_reason' => 'string',
                'can_contact_time' => 'string',
                'imei' => 'string'
            ],
            self::SCENARIO_CALCULATE => [
                'order_id' => 'required|integer|exists:order,id',
                'principal' => 'required|numeric',
                'loan_days' => 'required|integer',
            ],
            self::SCENARIO_AGREEMENT => [
                'loan_amount' => 'required|numeric',
                'loan_days' => 'required|integer',
            ],
            self::SCENARIO_REPAYMENT_PLAN => [
                'order_id' => 'required|integer',
            ],
            self::SCENARIO_REDUCTION => [
                'order_id' => 'required|integer',
            ],
            self::SCENARIO_LAST_ORDER => [
                'order_id' => 'required|integer',
            ],
            self::SCENARIO_TRIAL => [
                'loanAmt' => 'required|numeric',
                'loanDay' => 'required|integer',
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
                'principal.required' => t('请选择借款金额', 'rule'),
                'loan_days.required' => t('请选择借款天数', 'rule'),
            ],
        ];
    }

    public function attributes()
    {
        return [
            'client_id' => '终端标识'
        ];
    }
}
