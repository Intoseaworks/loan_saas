<?php

namespace Api\Rules\Captcha;

use Common\Redis\Captcha\SmsCaptchaRedis;
use Common\Rule\Rule;

class CaptchaRule extends Rule
{

    const SCENARIO_SMS_CODE = 'sms_code';
    const SCENARIO_SEND_SMS = 'send_sms';
    const SCENARIO_SEND_MAIL = 'send_email';
    const SCENARIO_SEND_VOICE = 'send_voice';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_SMS_CODE => [
                'telephone' => 'required|mobile',
            ],
            self::SCENARIO_SEND_SMS => [
                'telephone' => 'required|mobile',
                'use' => 'in:' . implode(',', SmsCaptchaRedis::USE),
            ],
            self::SCENARIO_SEND_MAIL => [
                'email' => 'required|email',
            ],
            self::SCENARIO_SEND_VOICE => [
                'telephone' => 'required|mobile'
            ]
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
            self::SCENARIO_SMS_CODE => [
                'telephone.required' => t('请输入手机号', 'captcha'),
                'telephone.mobile' => t('手机号格式不正确', 'captcha'),
            ],
            self::SCENARIO_SEND_SMS => [
                'telephone.required' => t('请输入手机号', 'captcha'),
                'telephone.mobile' => t('手机号格式不正确', 'captcha'),
            ],
            self::SCENARIO_SEND_MAIL => [
                'email.email' => t('邮箱不正确', 'captcha'),
            ],
        ];
    }
}
