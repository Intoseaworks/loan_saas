<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 16:47
 */

namespace Api\Controllers\Login;


use Api\Models\User\User;
use Api\Rules\User\LoginRule;
use Api\Services\Login\LoginServer;
use Common\Exceptions\ApiException;
use Common\Redis\Captcha\SmsCaptchaRedis;
use Common\Response\ApiBaseController;
use Common\Utils\Data\StringHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoginController extends ApiBaseController
{
    public function login(LoginRule $rule)
    {
        $telephone = StringHelper::formatTelephone($this->getParam('telephone'));
        $attributes = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_LOGIN, $attributes)) {
            return $this->resultFail($rule->getError());
        }

        $loginService = LoginServer::server();
        if ($userModel = User::model()->getByTelephone($telephone)) {
            $data = $loginService->login($userModel);
            //$loginService->setUserClientId($userModel, array_get($attributes, 'client_id'));
            return $this->resultSuccess($data, '登录成功，更多额度待激活');
        }
        $data = $loginService->register($attributes);

        return $this->resultSuccess($data, '注册成功，更多额度待激活');
    }

    public function logout()
    {
        //LoginServer::server()->logout();
        return $this->resultSuccess([], '退出登录成功');
    }

    public function reLogin()
    {
        throw new ApiException(t('登录已过期，请重新登录'), 1403);
        $token = $this->request->get('token');
        return $this->resultSuccess(LoginServer::server()->refreshToken($token), '登录续期成功');
    }

    public function test()
    {
        $this->identity();
        return $this->resultSuccess([
            'id' => Auth::guard('api')->id(),
            'username' => Auth::guard('api')->user(),
        ], '用户信息');
    }

    public function loginTest()
    {
        $user = \Api\Models\User\User::find(2153);
        $token = Auth::guard('api')->login($user);
        if (!$token) {
            return $this->result(400, '登录失败');
        }
        return $this->resultSuccess([
            'token' => $token
        ]);
    }

    public function loginByPwd(LoginRule $rule) {
        $telephone = StringHelper::formatTelephone($this->getParam('telephone'));
        $attributes = $this->getParams();
        if (!$rule->validate($rule::PWD_LOGIN, $attributes)) {
            return $this->resultFail($rule->getError());
        }
        $data = [];
        $loginService = LoginServer::server();
        if ($userModel = User::model()->getByTelephone($telephone)) {
            if ($userModel->password == $loginService::buildPwdMd5($telephone, $this->getParam('password'))) {
                $data = $loginService->login($userModel);
                $loginService->setUserClientId($userModel, array_get($attributes, 'client_id'));
                return $this->resultSuccess($data, '登录成功，更多额度待激活');
            } else {
                return $this->resultFail('密码输入错误');
            }
        } else {
            return $this->resultFail('该手机号未注册');
        }
    }

    public function reg(LoginRule $rule, $isChangePwd = FALSE)
    {
        $telephone = StringHelper::formatTelephone($this->getParam('telephone'));
        $use = $isChangePwd ? SmsCaptchaRedis::USE_FORGOT_PASSWORD : SmsCaptchaRedis::USE_APP_LOGIN;
        $this->request->offsetSet('use', $use);
        $attributes = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_REG, $attributes)) {
            return $this->resultFail($rule->getError());
        }
        $data = [];
        if ($this->getParam('password') != $this->getParam('password_check')) {
            return $this->resultFail(t('密码不一致', 'exception'));
        }
        $loginService = LoginServer::server();
        if ($userModel = User::model()->getByTelephone($telephone)) {
            if ($isChangePwd) {
                $userModel->password = $loginService::buildPwdMd5($telephone, $this->getParam('password'));
                $userModel->save();
                return $this->resultSuccess($data, 'Password modified successfully');
            } else {
                return $this->resultFail(t('号码已注册，注册失败', 'exception'));
            }
        } else {
            if ($isChangePwd) {
                return $this->resultFail('Password modification failed. The mobile phone number is not registered.');
            }else{
                $attributes['password'] = $loginService::buildPwdMd5($telephone, $this->getParam('password'));
                $data = $loginService->register($attributes);
                return $this->resultSuccess($data, '注册成功，更多额度待激活');
            }
        }
    }

    public function regWeb(LoginRule $rule, $isChangePwd = FALSE)
    {
        $telephone = StringHelper::formatTelephone($this->getParam('telephone'));
        $use = $isChangePwd ? SmsCaptchaRedis::USE_FORGOT_PASSWORD : SmsCaptchaRedis::USE_APP_LOGIN_WEB;
        $this->request->offsetSet('use', $use);
        $attributes = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_REG_WEB, $attributes)) {
            return $this->resultFail($rule->getError());
        }
        $data = [];
        $loginService = LoginServer::server();
        if ($userModel = User::model()->getByTelephone($telephone)) {
            if ($isChangePwd) {
                if($userModel->password){
                    $userModel->password = $loginService::buildPwdMd5($telephone, $this->getParam('password'));
                    $userModel->save();
                    return $this->resultSuccess($data, 'Password modified successfully');
                }else{
                    return $this->resultFail('Password modification failed. The mobile phone number is not registered.');
                }
            } else {
                return $this->resultFail(t('号码已注册，注册失败', 'exception'));
            }
        } else {
            $attributes['password'] = $loginService::buildPwdMd5($telephone, 'a'.Str::substr($telephone,-5));
            $data = $loginService->register($attributes);
            return $this->resultSuccess($data, '注册成功，更多额度待激活');
        }
    }

    public function forgotPwd(LoginRule $rule) {
        return $this->reg($rule, TRUE);
    }

}
