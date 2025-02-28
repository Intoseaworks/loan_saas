<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\Risk;

use Api\Models\User\UserAuth;
use Api\Services\BaseService;
use Api\Services\User\UserAuthServer;

class RiskServer extends BaseService
{
    /**
     * @param $param
     * @return mixed
     */
    public function authTime($param)
    {
        //已验证通过，忽略验证中
        if ($param['authStatus'] == UserAuth::AUTH_STATUS_IN) {
            $authStatus = UserAuthServer::server()->getAuthStatus($param['userId'], $param['authName']);
            if ($authStatus == UserAuth::STATUS_VALID) {
                return;
            }
        }
        return UserAuthServer::server()->setAuth($param['userId'], $param['authName'], $param['time'], $param['authStatus']);
    }

}
