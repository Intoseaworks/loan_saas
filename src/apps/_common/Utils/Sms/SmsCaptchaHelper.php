<?php

namespace Common\Utils\Sms;

use Api\Services\User\UserCheckServer;
use Common\Exceptions\ApiException;
use Common\Models\User\User;
use Common\Redis\Captcha\SmsCaptchaRedis;
use Common\Redis\CommonRedis;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Helper;
use Common\Utils\Host\HostHelper;
use Yunhan\Utils\Env;

/**
 * Class SmsCaptchaHelper
 * @package App\Helper\Third\Sms
 * @author ChangHai Zhan
 */
class SmsCaptchaHelper
{
    use Helper;

    public $value = [];

    /**
     * @param $number
     * @return int
     */
    public static function getCaptchaRandCode($number = 4)
    {
        $min = 1;
        $max = 9;
        $number -= 1;
        for ($i = 0; $i < $number; $i++) {
            $min .= '0';
            $max .= '9';
        }
        return mt_rand((int)$min, (int)$max);
    }

    /**
     * @return bool
     */
    public function hasSmsOn()
    {
        return config('config.has_sms_on');
    }

    /**
     * @param $phone
     * @param $code
     * @param string $use
     * @param $userId
     * @param array $options
     * @param $type
     * @return bool
     */
    public function send($phone, $code, $use, $userId = null, $options = [], $type = 'sms')
    {
        //加载原来的配置
        $this->getValue($use, $phone, $userId);
        if (!$this->validateDaySendMax()) {
            return false;//由于短信通道问题0804取消这个判断
        }
        if (!$this->validateIntervalTime()) {
            return false;
        }
        $this->restoreValue();
        $this->value = array_merge($options, $this->value);
        $this->getInitValue($this->value);

        if(!($user = User::model()->getOne(['telephone' => $phone]))){
            UserCheckServer::server()->canRegister();
        }

        if($user && in_array($use , [SmsCaptchaRedis::USE_APP_LOGIN ,SmsCaptchaRedis::USE_APP_LOGIN_WEB])){
            $this->addError(t('已经注册请登录', 'captcha'));
            return false;
        }

        $ip = HostHelper::getIp();
        $ipCaptchaCount = CommonRedis::redis()->verifyCount("sms:captcha:{$ip}:");
        if ($ipCaptchaCount >= 50) {
            DingHelper::notice([
                'ip' => $ip,
                'phone' => $phone,
                'count' => $ipCaptchaCount
            ], '【告警】ip大量请求短信');
            return true;
        }

        /** 关闭短信默认验证码1234 */
        $code = self::hasSmsOn() ? $code : $this->getTestCode();

        if ($type == 'sms' && !SmsHelper::helper()->sendCaptcha($phone, $code, $use)) {
            $this->addError(t('发送验证码异常,请稍后再试', 'captcha'));
            return false;
        }
        if ($type == 'voice' && !SmsHelper::helper()->sendVoiceCaptcha($phone, $code, $use)) {
            $this->addError(t('发送验证码异常,请稍后再试', 'captcha'));
            return false;
        }

        $this->value['code'] = $code;
        if (!$this->set($use, $phone, $userId, $this->value)) {
            $this->addError(t('发送验证码异常,请稍后再试', 'captcha'));
            return false;
        }
        return true;
    }

    /**
     * @param $use
     * @param $phone
     * @param $userId
     * @return array|mixed
     */
    public function getValue($use, $phone, $userId)
    {
        if (!$this->value) {
            if ($value = SmsCaptchaRedis::redis()->get($use, $phone, $userId)) {
                $this->value = ArrayHelper::jsonToArray($value);
            }
        }
        return $this->value;
    }

    /**
     * 验证每天最大发送次数
     *
     * @return bool
     */
    protected function validateDaySendMax()
    {
        if (!$this->isValidate()) {
            return true;
        }
        if (!isset($this->value['daySendMax'])) {
            return true;
        }
        $this->value['daySendMax'] -= 1;
        if (!Env::isDev() && $this->value['daySendMax'] < 0) {
            $this->addError(t('当天短信验证码次数超限,请明天再试', 'captcha'));
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    protected function isValidate()
    {
        return true;
    }

    /**
     * 验证发送间隔
     *
     * @return bool
     */
    protected function validateIntervalTime()
    {
        if (!$this->isValidate()) {
            return true;
        }
        if (!isset($this->value['intervalTime'])) {
            return true;
        }
        if (!Env::isDev() && $this->value['intervalTime'] > time()) {
            $this->addError(t('1分钟内不能频繁发送验证码', 'captcha'));
            return false;
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function restoreValue()
    {
        $attributes = [
            'expireTime',
            'intervalTime',
            'errorMax',
            'code',
        ];
        foreach ($attributes as $attribute) {
            unset($this->value[$attribute]);
        }
        return $this->value;
    }

    /**
     * 配置 短信过期时间 最大错误次数 每天能发送次数 短信发送间隔 用途
     * @param array $value
     * @return array
     */
    public function getInitValue($value = [])
    {
        $this->value = [
            'expireTime' => $value['expireTime'] ?? time() + 60 * 20,
            'errorMax' => $value['errorMax'] ?? 5,
            'daySendMax' => $value['daySendMax'] ?? 10,
            'intervalTime' => $value['intervalTime'] ?? time() + 60 * 1,
        ];
        return $this->value;
    }

    /**
     * @return int
     */
    public function getTestCode()
    {
        return 1234;
    }

    /**
     * @param $use
     * @param $phone
     * @param $userId
     * @param $value
     * @return mixed
     */
    public function set($use, $phone, $userId, $value)
    {
        return SmsCaptchaRedis::redis()->set($use, $phone, $userId, json_encode($value));
    }

    /**
     * 验证短信
     *
     * @param $code
     * @param $phone
     * @param null $userId
     * @param string $use
     * @return bool
     */
    public function validate($code, $phone, $userId = null, $use = SmsCaptchaRedis::USE_APP_LOGIN)
    {
        //加载原来的配置
        $this->getValue($use, $phone, $userId);
        if (!$this->validateExpireTime()) {
            throw new ApiException($this->getError());
        }
        if (!$this->validateErrorMax()) {
            throw new ApiException($this->getError());
        }
        if (!$this->validateCode($code)) {
            $this->value['errorMax'] -= 1;
            $this->set($use, $phone, $userId, $this->value);
            throw new ApiException($this->getError());
        }
        unset($this->value['expireTime']);
        unset($this->value['code']);
        $this->set($use, $phone, $userId, $this->value);
        return true;
    }

    /**
     * 验证短信是否过期
     *
     * @return bool
     */
    protected function validateExpireTime()
    {
        if (!$this->isValidate()) {
            return true;
        }
        if (!isset($this->value['expireTime'])) {
            $this->addError(t('验证码不正确', 'captcha'));
            return false;
        }
        if ($this->value['expireTime'] < time()) {
            $this->addError(t('验证码过期', 'captcha'));
            return false;
        }
        return true;
    }

    /**
     * 验证错误次数
     *
     * @return bool
     */
    protected function validateErrorMax()
    {
        if (!$this->isValidate()) {
            return true;
        }
        if (!isset($this->value['errorMax'])) {
            $this->addError('短信验证码错误');
            return false;
        }
        if ($this->value['errorMax'] <= 0) {
            $this->addError(t('验证码不正确', 'captcha'));
            return false;
        }
        return true;
    }

    /**
     * 验证短信验证码是否正确
     *
     * @param $code
     * @return bool
     */
    protected function validateCode($code)
    {
        if (!isset($this->value['code'])) {
            $this->addError(t('验证码不正确', 'captcha'));
            return false;
        }
        if ($this->value['code'] != $code) {
            $this->addError(t('验证码不正确', 'captcha'));
            return false;
        }
        return true;
    }
}
