<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 16:47
 */

namespace Api\Controllers\Captcha;

use Api\Models\User\User;
use Api\Rules\Captcha\CaptchaRule;
use Api\Services\Captcha\CaptchaServer;
use Common\Redis\Captcha\SmsCaptchaRedis;
use Common\Response\ApiBaseController;

class CaptchaController extends ApiBaseController
{
    public function sendSmsCode(CaptchaRule $rule)
    {
        if (!$rule->validate(CaptchaRule::SCENARIO_SMS_CODE, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }
        $telephone = $this->getParam('telephone');
        $use = $this->getParam('use', SmsCaptchaRedis::USE_APP_LOGIN);
        if ($use == SmsCaptchaRedis::USE_FORGOT_PASSWORD && !User::model()->getByTelephone($telephone)) {
            return $this->resultFail(t('该手机号未注册', 'exception'));
        }
        if ($use == SmsCaptchaRedis::USE_APP_LOGIN && User::model()->getByTelephone($telephone)) {
            return $this->resultFail(t('号码已注册，注册失败', 'exception'));
        }
        CaptchaServer::server()->sendSmsCode($telephone, $use);
        return $this->resultSuccess([], t('验证码发送成功', 'captcha'));
    }

    /**
     * 统一发送短验接口
     * @param CaptchaRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Common\Exceptions\RuleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function sendSms(CaptchaRule $rule)
    {
        $params = $rule->validateE($rule::SCENARIO_SEND_SMS);

        $use = $this->request->get('use', SmsCaptchaRedis::USE_APP_LOGIN);

        CaptchaServer::server()->sendSms($params['telephone'], $use);

        return $this->resultSuccess([], t('验证码发送成功', 'captcha'));
    }

    /**
     * h5统一发送短验接口
     * @param CaptchaRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Common\Exceptions\RuleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function sendSmsWeb(CaptchaRule $rule)
    {
        $params = $rule->validateE($rule::SCENARIO_SEND_SMS);

        $use = $this->request->get('use', SmsCaptchaRedis::USE_APP_LOGIN_WEB);

        CaptchaServer::server()->sendSms($params['telephone'], $use);

        return $this->resultSuccess([], t('验证码发送成功', 'captcha'));
    }

    /**
     * @param CaptchaRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Common\Exceptions\RuleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function sendMail(CaptchaRule $rule)
    {
        $params = $rule->validateE($rule::SCENARIO_SEND_MAIL);
        CaptchaServer::server()->sendMail($params['email']);

        return $this->resultSuccess([], t('验证码发送成功', 'captcha'));
    }

    /**
     * @param CaptchaRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Common\Exceptions\RuleException
     */
    public function sendVoice(CaptchaRule $rule)
    {
        $params = $rule->validateE($rule::SCENARIO_SEND_VOICE);
        CaptchaServer::server()->sendVoice($params['telephone']);

        return $this->resultSuccess([], t('验证码发送成功', 'captcha'));
    }

}
