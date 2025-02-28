<?php

namespace Api\Rules\User;

use Api\Models\BankCard\BankCardPeso;
use Api\Models\Upload\Upload;
use Common\Rule\Rule;
use Common\Utils\Data\DateHelper;
use Common\Validators\Validation;
use Illuminate\Http\Request;
use Validator;

class UserInitRule extends Rule {

    /**
     * 验证场景 create
     */
    const STEP_ONE = 'step_one';
    const STEP_ONE_B = 'step_one_b';
    const STEP_TWO = 'step_two';
    const STEP_TWO_U4 = 'step_two_u4';
    const STEP_TWO_NEW = 'step_two_new';
    const STEP_THREE = 'step_three';
    const STEP_THREE_U4 = 'step_three_u4';
    const STEP_THREE_U4_NEW = 'step_three_u4_new';
    const STEP_FOUR = 'step_four';
    const STEP_FIVE = 'step_five';
    const STEP_SIX = 'step_six';
    const SCENARIO_INIT = 'init';
    const SCENARIO_BARCODE = 'barcode';

    /**
     * @return array
     */
    public function rules() {
        $this->extend();
        $telephone = app(Request::class)->get('telephone');
        return [
            self::STEP_ONE => [
                'birthday' => 'required|validate_birthday',
                'gender' => 'required',
                'marital_status' => 'required',
                'education_level' => 'required',
                'social_software_phone' => 'mobile',
                'fullname' => 'required',
                'email' => 'required',
                'facebook_messager' => 'string',
            ],
            self::STEP_ONE_B => [
                'birthday' => 'required|validate_birthday',
                'gender' => 'required',
                'marital_status' => 'required',
                'education_level' => 'required',
                'social_software_phone' => 'mobile',
                'firstname' => 'required',
                'lastname' => 'required',
                'email' => 'required',
                'facebook_messager' => 'string',
            ],
            self::STEP_TWO => [
                'province' => 'required',
                'city' => 'required',
//                'district' => 'required',
                'address' => 'required',
                'live_length' => 'required',
                'contact_fullname1' => 'required|min:2',
                'relation1' => 'required',
                'contact_telephone1' => 'required|mobile',
                'contact_fullname2' => 'required|min:2',
                'relation2' => 'required',
                'contact_telephone2' => 'required|mobile|different:contact_telephone1',
                'contact_fullname3' => 'required|min:2',
                'relation3' => 'required',
                'contact_telephone3' => 'required|mobile|different:contact_telephone1|different:contact_telephone2',
            ],
            self::STEP_TWO_U4 => [
                'province' => 'required',
                'city' => 'required',
//                'district' => 'required',
                'address' => 'required',
                'live_length' => 'required',
            ],
            self::STEP_TWO_NEW => [
                'province' => 'required',
                'city' => 'required',
//                'district' => 'required',
                'address' => 'required',
                'live_length' => 'required',
                'employment_type' => 'required',
//                'industry' => 'required',
//                'company' => 'required',
//                'salary' => 'required',
//                'working_time_type' => 'required',
                'work_phone' => 'numeric|digits_between:6,11',
                'loan_reason' => 'string',
            ],
            self::STEP_THREE => [
                'employment_type' => 'required',
//                'industry' => 'required',
//                'company' => 'required',
//                'salary' => 'required',
//                'working_time_type' => 'required',
                'work_phone' => 'numeric|digits_between:6,11',
                'loan_reason' => 'string',
            ],
            self::STEP_THREE_U4 => [
                'employment_type' => 'required',
//                'industry' => 'required',
//                'company' => 'required',
//                'salary' => 'required',
//                'working_time_type' => 'required',
                'work_phone' => 'numeric|digits_between:6,11',
                'contact_fullname1' => 'required|min:2',
                'relation1' => 'required',
                'contact_telephone1' => 'required|mobile',
                'contact_fullname2' => 'required|min:2',
                'relation2' => 'required',
                'contact_telephone2' => 'required|mobile|different:contact_telephone1',
                'contact_fullname3' => 'required|min:2',
                'relation3' => 'required',
                'contact_telephone3' => 'required|mobile|different:contact_telephone1|different:contact_telephone2',
            ],
            self::STEP_THREE_U4_NEW => [
                'work_phone' => 'numeric|digits_between:6,11',
                'contact_fullname1' => 'required|min:2',
                'relation1' => 'required',
                'contact_telephone1' => 'required|mobile',
                'contact_fullname2' => 'required|min:2',
                'relation2' => 'required',
                'contact_telephone2' => 'required|mobile|different:contact_telephone1',
                'contact_fullname3' => 'required|min:2',
                'relation3' => 'required',
                'contact_telephone3' => 'required|mobile|different:contact_telephone1|different:contact_telephone2',
            ],
            self::STEP_SIX => [
                'type' => 'required|in:' . implode(',', array_keys(BankCardPeso::PAYMENT_TYPE)),
                'bank_no' => 'numeric|required_if:type,' . BankCardPeso::PAYMENT_TYPE_BANK,
                'bank_code' => 'required_if:type,' . BankCardPeso::PAYMENT_TYPE_BANK,
                'institution_name' => 'required_if:type,' . BankCardPeso::PAYMENT_TYPE_CASH,
                'company_id' => 'mimes:' . implode(',', Upload::EXTENSION),
                'channel_name' => 'required_if:type,' . BankCardPeso::PAYMENT_TYPE_OTHER,
            ],
            self::SCENARIO_INIT => [
                'telephone' => 'required|mobile',
                'loan_days' => 'required',
                'principal' => 'required',
                'id_card_no' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        $reg = "/(^\d{9}$)|(^\d{12}$)/";
                        if (!preg_match($reg, $value, $matches)) {
                            return $fail('身份证格式不正确');
                        }
                    },
                ]
            ],
            self::SCENARIO_BARCODE => [
                'amount' => 'required|numeric|min:100',
                'reference_no' => 'required'
            ]
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages() {
        return [
            self::STEP_ONE => [
                'social_software_phone.mobile' => t('手机号格式不正确', 'captcha'),
            ],
            self::STEP_THREE_U4 => [
                'contact_telephone1.mobile' => t('手机号格式不正确', 'captcha').'contact_telephone1',
                'contact_telephone2.mobile' => t('手机号格式不正确', 'captcha').'contact_telephone2',
                'contact_telephone3.mobile' => t('手机号格式不正确', 'captcha').'contact_telephone3',
            ],
            self::STEP_THREE_U4_NEW => [
                'contact_telephone1.mobile' => t('手机号格式不正确', 'captcha').'contact_telephone1',
                'contact_telephone2.mobile' => t('手机号格式不正确', 'captcha').'contact_telephone2',
                'contact_telephone3.mobile' => t('手机号格式不正确', 'captcha').'contact_telephone3',
            ],
            self::STEP_TWO => [
                'contact_telephone1.mobile' => t('手机号格式不正确', 'captcha'),
                'contact_telephone2.mobile' => t('手机号格式不正确', 'captcha'),
                'contact_telephone3.mobile' => t('手机号格式不正确', 'captcha'),
            ],
            self::SCENARIO_INIT => [
                'social_software_phone.mobile' => t('手机号格式不正确', 'captcha'),
            ],
            self::STEP_SIX => [
//                'bank_no.digits_between' => "The bank account no must be between 10 and 13 digits，pls re-enter",
            ],
        ];
    }

    public function attributes() {
        return [];
    }

    protected function extend() {

        Validator::extendImplicit('validate_birthday', function ($attribute, $value, $parameters, Validation $validator) {
            if (!DateHelper::checkDateIsValid($value)) {
                $validator->setCustomMessages(['validate_birthday' => t('非法日期')]);
                return false;
            }

            $timestamp = strtotime(str_replace('/', '-', $value));
            if ($timestamp > time()) {
                $validator->setCustomMessages(['validate_birthday' => 'Cannot select future date']);
                return false;
            }

            return true;
        });
    }

}
