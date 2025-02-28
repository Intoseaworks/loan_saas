<?php

namespace Api\Rules\User;

use Common\Rule\Rule;
use Common\Utils\Data\StringHelper;

class BankcardRule extends Rule
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_LOOK_UP_IFSC = 'lookUpIfsc';
    const SCENARIO_GET_BRANCH = 'getBranch';
    const STEP_SIX = 'step_six';

    /**
     * @return array
     */
    public function rules()
    {
        $this->extendImplicit('bankCardValidate', function ($attribute, $value, $parameters, $validator) {
            /** @var \Common\Validators\Validation $validator */

            $cardNo = StringHelper::delSpace($value);
            if (!$validator->validateRegex($attribute, $cardNo, ["/^\d{6,20}$/"])) {
                return false;
            }
            return true;
        }, '卡号格式不正确');

        return [
            self::SCENARIO_CREATE => [
                'card_no' => 'required|bankCardValidate',
                'ifsc' => 'required|exists:bank_info,ifsc',
            ],
            self::SCENARIO_LOOK_UP_IFSC => [
                'bank' => 'required',
                'state' => 'string',
                'city' => 'string',
                'branch' => 'string',
                'ifsc' => 'string',
            ],
            self::SCENARIO_GET_BRANCH => [
                'state' => 'required',
                'city' => 'required',
            ],
            self::STEP_SIX => [
//                'type' => 'required|in:' . implode(',', array_keys(BankCardPeso::PAYMENT_TYPE)),
                'bank_no' => 'numeric|digits_between:1,100',#|required_if:type,' . BankCardPeso::PAYMENT_TYPE_BANK
//                'bank_code' => 'required_if:type,' . BankCardPeso::PAYMENT_TYPE_BANK,
//                'institution_name' => 'required_if:type,' . BankCardPeso::PAYMENT_TYPE_CASH,
//                'company_id' => 'mimes:' . implode(',', Upload::EXTENSION),
//                'channel_name' => 'required_if:type,' . BankCardPeso::PAYMENT_TYPE_OTHER,
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
                'card_no.required' => t('请填写卡号', 'rule'),
                'ifsc.required' => t('请选择或填写ifsc', 'rule'),
                'ifsc.exists' => t('ifsc不正确，请重新选择或填写', 'rule'),
            ],
            self::SCENARIO_LOOK_UP_IFSC => [
                'bank.required' => t('请输入银行名称', 'rule'),
            ],
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
