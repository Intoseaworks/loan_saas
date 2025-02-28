<?php


namespace Api\Services\Common;


use Api\Services\BaseService;
use Cache;
use Common\Exceptions\ApiException;
use Common\Utils\Email\EmailHelper;
use Common\Utils\Sms\SmsHelper;

# 该类废弃，对应方法已迁移至Captcha/CaptchaServer
# 该类废弃，对应方法已迁移至Captcha/CaptchaServer
# 该类废弃，对应方法已迁移至Captcha/CaptchaServer
class CaptchaServer extends BaseService
{
    const CACHE_KEY_SMS = 'sms';
    const CACHE_KEY_EMAIL = 'email';
    const CACHE_KEY_VOICE = 'voice';
    // 发送频率间隔
    const RESEND_SEC = 60;
    // 当天上限次数
    const ONE_DAY_FREQ = 10;
    // 验证码缓存过期时间;
    const CACHE_CAPTCHA_TIMEOUT = 60 * 60 * 24;

    /**
     * 发送短信验证码
     *
     * @param $telephone
     * @throws ApiException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function sendSms($telephone)
    {
        $captcha = $this->getCaptchaCode(static::CACHE_KEY_SMS, $telephone);
        $sendCount = array_get($captcha, 'check', 0);
        $time = time();
        if ($captcha && $captcha['time'] + static::RESEND_SEC > $time) {
            throw new ApiException(t('1分钟内不能频繁发送验证码', 'captcha'));
        }
        if ($captcha && $captcha['check'] > static::ONE_DAY_FREQ) {
            throw new ApiException(t('当天语音验证码次数超限,请明天再试', 'captcha'));
        }

        $code = $this->getRandomCaptcha();
        if (!SmsHelper::helper()->sendCaptcha($telephone, $code)) {
            throw new ApiException(t('发送验证码异常,请稍后再试', 'captcha'));
        }

        $this->cacheCode($captcha, $sendCount, $this->getCacheKey(static::CACHE_KEY_SMS, $telephone));
    }

    /**
     * 发送邮件
     *
     * @param $email
     * @return bool
     * @throws ApiException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function sendMail($email)
    {
        $captcha = $this->getCaptchaCode(static::CACHE_KEY_EMAIL, $email);
        $sendCount = array_get($captcha, 'check', 0);
        $time = time();
        if ($captcha && $captcha['time'] + static::RESEND_SEC > $time) {
            throw new ApiException(t('1分钟内不能频繁发送验证码', 'captcha'));
        }
        if ($captcha && $captcha['check'] > static::ONE_DAY_FREQ) {
            throw new ApiException(t('当天邮箱验证码次数超限,请明天再试', 'captcha'));
        }

        $content = t('邮箱验证码发送文案', 'captcha', ['code' => $this->getRandomCaptcha()]);
        EmailHelper::send($content, 'Cash-Now Captcha', $email);

        $this->cacheCode($captcha, $sendCount, $this->getCacheKey(static::CACHE_KEY_EMAIL, $email));

        return true;
    }

    /**
     * 发送语音验证码
     *
     * @param $telephone
     * @throws ApiException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function sendVoice($telephone)
    {
        $captcha = $this->getCaptchaCode(static::CACHE_KEY_VOICE, $telephone);
        $sendCount = array_get($captcha, 'check', 0);
        $time = time();
        if ($captcha && $captcha['time'] + static::RESEND_SEC > $time) {
            throw new ApiException(t('1分钟内不能频繁发送验证码', 'captcha'));
        }
        if ($captcha && $captcha['check'] > static::ONE_DAY_FREQ) {
            throw new ApiException(t('当天语音验证码次数超限,请明天再试', 'captcha'));
        }

        $code = $this->getRandomCaptcha();
        if (!SmsHelper::helper()->sendVoiceCaptcha($telephone, $code)) {
            throw new ApiException(t('发送验证码异常,请稍后再试', 'captcha'));
        }

        $this->cacheCode($captcha, $sendCount, $this->getCacheKey(static::CACHE_KEY_VOICE, $telephone));
    }

    /**
     * 获取验证码
     *
     * @param $prefix
     * @param $value
     * @return mixed
     */
    public function getCaptchaCode($prefix, $value)
    {
        $cacheKeyName = $this->getCacheKey($prefix, $value);

        return Cache::get($cacheKeyName);
    }

    /**
     * 获取缓存key
     *
     * @param $prefix
     * @param $value
     * @return string
     */
    public function getCacheKey($prefix, $value)
    {
        return $prefix . $value . date('Ymd');
    }

    /**
     * 获取随机验证码
     *
     * @return string
     */
    protected function getRandomCaptcha()
    {
        return sprintf('%04s', mt_rand(100000, 999999));
    }

    /**
     * @param $captcha
     * @param $count
     * @param $key
     * @param float|int $expire
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function cacheCode($captcha, $count, $key, $expire = self::CACHE_CAPTCHA_TIMEOUT)
    {
        $store = ['captcha' => $captcha, 'check' => $count + 1, 'last_check' => time(), 'time' => time()];
        Cache::set($key, $store, $expire);

        return true;
    }

    /**
     * 验证码验证
     *
     * @param $prefix
     * @param $value
     * @param $captcha
     * @return bool
     */
    public function validCaptcha($prefix, $value, $captcha)
    {
        return array_get($this->getCaptchaCode($prefix, $value), 'captcha') == $captcha;
    }

}
