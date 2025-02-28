<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/18
 * Time: 9:42
 */

namespace Tests\Api\Common;


use Api\Models\User\User;
use Common\Redis\Captcha\SmsCaptchaRedis;
use Tests\Api\TestBase;

class LoginTest extends TestBase
{
    public function testLogin()
    {
        $loginParams = [
            'telephone' => 8979465154,
            'captcha' => '1234',
            'client_id' => 'h5',
            'channel' => 'ss01',
            'platform' => 'h5',
        ];

        $this->post('app/login', $loginParams)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
        dd('s');


        /** 手机号码格式错误 */
        $this->post('app/login', $loginParams)->seeJson([
            'code' => self::ERROR_CODE,
        ]);
        $loginParams['telephone'] = '188' . rand(10000000, 99999999);
        /** 未获取验证码 */
        if (!$this->logined()) {
            $this->post('app/login', $loginParams)->seeJson([
                'code' => self::ERROR_CODE,
            ]);
        }
        /** 获取验证码 */
        $this->post('app/send-sms-code', ['telephone' => $loginParams['telephone']])->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
        /** 登录成功 */
        $data = $this->post('app/login', $loginParams)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();

        $this->user = User::model()->getOne($data['userId']);
        $this->token = $this->getToken($data['userId']);
        return $this;
    }

    public function testReLogin()
    {
        $this->post('app/re-login', ['token' => $this->getToken()])->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    /** 退出登录 */
    public function testLogout()
    {
        $this->get('app/logout')->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

    /**
     * 发送短信
     */
    public function testSendCode()
    {
        /** 获取验证码 忘记密码 */
        $this->post('app/send-sms-code', ['telephone' => 9999911118, 'use' => SmsCaptchaRedis::USE_FORGOT_PASSWORD])->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }
}
