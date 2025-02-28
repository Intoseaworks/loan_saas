<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Tests\Services\User;

use Api\Services\Data\DataServer;
use Tests\Api\Data\DataTest;
use Tests\Api\Order\AuthTest;
use Tests\Api\User\UserTest;
use Tests\Services\BaseService;

class UserDataTestServer extends BaseService
{
    /**
     * 认证全流程
     *
     * @param $userId
     */
    public function auth($userId)
    {
        // 认证流程
        $dataTest = new DataTest();
        $dataTest->setUp();
        $dataTest->testBankcardCreate();
        $dataTest->testIdentity();
        $dataServer = new DataServer();
        $dataServer->setIdentityStatus($userId);
        # begin 认证用户信息
        $userTest = new UserTest();
        $userTest->setUp();
        $userTest->testUserInfoCreate(); // 坑：需要重新new，不然通过不了Rule
        # end 认证用户信息
        $dataServer->setTelephoneStatus($userId);
        # 请求认证列表
        $authTest = new AuthTest();
        $authTest->setUp();
        $authTest->testFaqIndex();
    }
}
