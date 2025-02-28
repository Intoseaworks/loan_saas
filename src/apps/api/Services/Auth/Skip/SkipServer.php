<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/14
 * Time: 15:50
 */

namespace Api\Services\Auth\Skip;


use Api\Models\Upload\Upload;
use Api\Models\User\User;
use Api\Models\User\UserAuth;
use Api\Services\Auth\Card\AuthCardServer;
use Api\Services\Auth\Card\CardCheckServer;
use Api\Services\BaseService;
use Api\Services\User\UserAuthServer;
use Common\Models\Third\ThirdPartyLog;
use Common\Utils\Services\AuthRequestHelper;
use Common\Utils\Upload\OssHelper;
use JMD\Libs\Services\DataFormat;

class SkipServer extends BaseService
{
    public $user;
    public $type;

    /**
     * @param $user
     * @param $type
     * @return OcrServer|void
     * @throws \Common\Exceptions\ApiException
     */
    public function auth($user, $type)
    {
        $this->user = $user;
        $this->type = $type;
        $fun = camel_case($this->type);
        if (!method_exists($this, $fun)) {
            return $this->outputException('type error');
        }
        call_user_func([$this, $fun]);
        return $this->outputSuccess('success', $this->data);
    }

    /**
     * @throws \Common\Exceptions\ApiException
     */
    public function address()
    {
        $ekycAuthStatus = UserAuthServer::server()->getAuth($this->user->id, UserAuth::TYPE_AADHAAR_CARD_KYC);
        if($ekycAuthStatus != UserAuth::AUTH_STATUS_SUCCESS){
            return $this->outputSuccess(t('不能跳过', 'auth'));
        }
        $authStatus = UserAuthServer::server()->getAuth($this->user->id, UserAuth::TYPE_ADDRESS);
        if($authStatus == UserAuth::AUTH_STATUS_SUCCESS){
            return $this->outputSuccess(t('已认证', 'auth'));
        }
        UserAuthServer::server()->setAuth($this->user, UserAuth::TYPE_ADDRESS, '', UserAuth::AUTH_STATUS_SKIP);
    }

    public function aadhaarKyc()
    {
        self::aadhaarKycSkip($this->user);
    }

    public static function aadhaarKycSkip(User $user)
    {
        $authStatus = UserAuthServer::server()->getAuth($user->id, UserAuth::TYPE_AADHAAR_CARD_KYC);
        if($authStatus == UserAuth::AUTH_STATUS_SUCCESS){
            return;
        }
        UserAuthServer::server()->setAuth($user, UserAuth::TYPE_AADHAAR_CARD_KYC, '', UserAuth::AUTH_STATUS_SKIP);
    }

}
