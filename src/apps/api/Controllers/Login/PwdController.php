<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 16:47
 */

namespace Api\Controllers\Login;

use Api\Models\User\User;
use Api\Rules\User\PwdLoginRule;
use Api\Services\Login\LoginServer;
use Api\Services\Login\PwdServer;
use Common\Response\ApiBaseController;
use Common\Utils\Data\StringHelper;

class PwdController extends ApiBaseController {

    public function loginByPwd(PwdLoginRule $rule) {
        $telephone = StringHelper::formatTelephone($this->getParam('telephone'));
        $attributes = $this->getParams();
        if (!$rule->validate($rule::PWD_LOGIN, $attributes)) {
            return $this->resultFail($rule->getError());
        }
        $data = [];
        $loginService = LoginServer::server();
        if ($userModel = User::model()->getByTelephone($telephone)) {
            if ($userModel->password == PwdServer::buildPwdMd5($telephone, $this->getParam('password'))) {
                $data = $loginService->login($userModel);
                $loginService->setUserClientId($userModel, array_get($attributes, 'client_id'));
                return $this->resultSuccess($data, t('登录成功，更多额度待激活', 'exception'));
            } else {
                return $this->resultFail(t('密码输入错误', 'exception'));
            }
        } else {
            return $this->resultFail(t('该手机号未注册', 'exception'));
        }
    }

    public function reg(PwdLoginRule $rule, $isChangePwd = FALSE) {
        $telephone = StringHelper::formatTelephone($this->getParam('telephone'));
        $attributes = $this->getParams();
        if (!$rule->validate($rule::SCENARIO_REG, $attributes)) {
            return $this->resultFail($rule->getError());
        }

        $data = [];
        if ($this->getParam('password') != $this->getParam('password_check')) {
            return $this->resultFail('Password inconsistency');
        }
        $loginService = LoginServer::server();
        if ($userModel = User::model()->getByTelephone($telephone)) {
            if ($isChangePwd) {
                if ($userModel->password) {
                    $userModel->password = PwdServer::buildPwdMd5($telephone, $this->getParam('password'));
                    $userModel->save();
                    return $this->resultSuccess($data, 'Password recovered successfully');
                } else {
                    return $this->resultFail('Password modification failed. The mobile phone number is not registered');
                }
            } else {
                return $this->resultFail('The number has been registered, failed to register');
            }
        } else {
            $attributes['password'] = PwdServer::buildPwdMd5($telephone, $this->getParam('password'));
            $data = $loginService->register($attributes);
            return $this->resultSuccess($data, t('注册成功，更多额度待激活', 'exception'));
        }
    }

    public function retrievePwd(PwdLoginRule $rule) {
        return $this->reg($rule, TRUE);
    }

    public function changePwd(PwdLoginRule $rule) {
        $user = $this->identity();
        $attributes = $this->getParams();
        if (!$rule->validate($rule::PWD_RETRIEVE, $attributes)) {
            return $this->resultFail($rule->getError());
        }
        if ( $this->getParam('new_password') == $this->getParam('check_password')) {
            $user->password = PwdServer::buildPwdMd5($user->telephone, $this->getParam('new_password'));
            $user->save();
            return $this->resultSuccess(null, 'Password modified successfully');
        } else {
            return $this->resultFail('Password verification failed');
        }
    }

}
