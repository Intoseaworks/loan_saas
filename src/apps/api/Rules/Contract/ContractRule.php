<?php

namespace Api\Rules\Contract;

use Common\Redis\Captcha\SmsCaptchaRedis;
use Common\Rule\Rule;

class ContractRule extends Rule
{
    const SCENARIO_GET_SIGN_URL = 'get_sign_url';
    const SCENARIO_SEND_CONTRACT = 'send_contract';
    const SCENARIO_GENERATE_CONTRACT = 'generate_contract';
    const SCENARIO_CONFIRM_OTP = 'confirm_otp';

    protected $telephone;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_GET_SIGN_URL => [
                //'orderId' => 'required',
            ],
            self::SCENARIO_SEND_CONTRACT => [
                'orderId' => 'required',
                'email' => 'required|email',
            ],
            self::SCENARIO_GENERATE_CONTRACT => [
                'orderId' => 'required',
            ],
            self::SCENARIO_CONFIRM_OTP => [
                'otp' => "required|string|size:4|captcha:{$this->telephone}," . SmsCaptchaRedis::USE_CONTRACT_CONFIRM,
            ]
        ];
    }

    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
            self::SCENARIO_SEND_CONTRACT => [
                'email.email' => '邮箱不正确',
            ],
            self::SCENARIO_CONFIRM_OTP => [
                'otp.required' => t('请输入验证码', 'captcha'),
                'otp.size' => t('验证码不正确', 'captcha'),
                'otp.captcha' => t('验证码不正确', 'captcha'),
            ],
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
