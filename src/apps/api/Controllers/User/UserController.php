<?php

namespace Api\Controllers\User;

use Api\Models\User\User;
use Api\Services\User\UserServer;
use Common\Response\ApiBaseController;
use Common\Services\Config\LoanMultipleConfigServer;

class UserController extends ApiBaseController
{
    public function home()
    {
        $this->identity();
        return $this->resultSuccess(UserServer::server()->home());
    }

    public function userIdentity()
    {
        $user = $this->identity();
        $params = $this->request->all();
        return $this->resultSuccess(UserServer::server()->identity($params));
    }

    public function riskProduct()
    {
        /** @var User $user */
        $user = $this->identity();
        $data = [
//            'riskDays' => Config::model()->getLoanDaysMax($user->quality),
//            'riskAmount' => Config::model()->getLoanAmountMax($user->quality),
            'riskDays' => LoanMultipleConfigServer::server()->getLoanDaysMax($user),
            'riskAmount' => LoanMultipleConfigServer::server()->getLoanAmountMax($user),
        ];
        return $this->resultSuccess($data, '风控额度产品获取成功');
    }

}
