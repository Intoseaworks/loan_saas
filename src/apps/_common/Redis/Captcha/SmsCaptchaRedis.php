<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 11:14
 */

namespace Common\Redis\Captcha;

use Common\Redis\BaseRedis;
use Common\Redis\RedisKey;
use Common\Utils\MerchantHelper;

class SmsCaptchaRedis
{
    use BaseRedis;

    /**
     * app 登录验证码
     */
    const USE_APP_LOGIN = 'app_login';
    /**
     * web 注册验证码
     */
    const USE_APP_LOGIN_WEB = 'app_login_web';
    /**
     * 合同签名otp
     */
    const USE_CONTRACT_CONFIRM = 'contract_confirm';
    /**
     * 找回密码
     */
    const USE_FORGOT_PASSWORD = 'forgot_password';

    const USE = [
        self::USE_APP_LOGIN,
        self::USE_CONTRACT_CONFIRM,
        self::USE_FORGOT_PASSWORD,
        self::USE_APP_LOGIN_WEB,
    ];

    /**
     * @param $use
     * @param $phone
     * @param null $userId
     * @return string
     */
    public function getKey($use, $phone, $userId = null)
    {
        if ($userId) {
            $userId = ':' . $userId;
        }
        $appId = MerchantHelper::getAppId() ?: 0;
        return RedisKey::SMS_CAPTCHA . $appId . ':' . date('Ymd') . ':' . $use . ':' . $phone . $userId;
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
        return $this->redis::set($this->getKey($use, $phone, $userId), $value, 'EX', 3600 * 24);
    }

    /**
     * @param $use
     * @param $phone
     * @param null $userId
     * @return mixed
     */
    public function get($use, $phone, $userId = null)
    {
        return $this->redis::get($this->getKey($use, $phone, $userId));
    }

    /**
     * @param $use
     * @param $phone
     * @param null $userId
     * @return mixed
     */
    public function del($use, $phone, $userId = null)
    {
        return $this->redis::del($this->getKey($use, $phone, $userId));
    }
}
