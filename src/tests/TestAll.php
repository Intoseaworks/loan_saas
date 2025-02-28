<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/2/18
 * Time: 10:24
 */

namespace Tests;


use Tests\Api\Common\ConfigTest;
use Tests\Api\Common\LocationTest;
use Tests\Api\Common\LoginTest;
use Tests\Api\Common\VersionTest;
use Tests\Api\Data\DataTest;
use Tests\Api\User\UserTest;
use Tests\Services\Order\CollectionTestServer;
use Tests\Services\Order\OrderTestServer;
use Tests\Services\Remit\RemitTestServer;
use Tests\Services\Repay\RepayTestServer;
use Tests\Services\User\UserDataTestServer;

class TestAll extends TestCase
{
    const ADMIN_ID = 1;

    public function testAll()
    {
        /** 初始化 **/
        /*$adminModel = AdminTradeAccount::firstOrNewModel([], ['id' => self::ADMIN_ID]);
        $adminModel->status = AdminTradeAccount::STATUS_ACTIVE;
        $adminModel->type = AdminTradeAccount::STATUS_ACTIVE;
        $adminModel->account_name = '测流程';
        $adminModel->save();*/

        /** 新用户流程 **/
        $loginTest = new LoginTest();
        $loginTest->setUp();
        // 登录
        $loginData = $loginTest->testLogin();
        $userId = $loginData->user->id;
        TestCase::$userId = $userId;

        // 上传用户信息
        $dataTest = new DataTest();
        $dataTest->setUp();
        $dataTest->testUserContact();
        $dataTest->testUserPosition();
        $dataTest->testUserSms();
        $dataTest->testUserAppList();
        $dataTest->testUserPhoneHardware();

        // 首页调用接口
        $userTest = new UserTest();
        $userTest->setUp();
        $userTest->testHome($userId);

        // 首页
        $configTest = new ConfigTest();
        $configTest->setUp();
        $configTest->testConfig();

        $versionTest = new VersionTest();
        $versionTest->setUp();
        $versionTest->testConfig();
        $locationTest = new LocationTest();
        $locationTest->setUp();
        $locationTest->testDistrictCode();

        # 认证流程
        UserDataTestServer::server()->auth($userId);
        # 借款流程
        $order = OrderTestServer::server()->loan($userId);
        $orderId = $order->id;
        # 出款流程
        RemitTestServer::server()->remit($orderId);
        # 催收流程
        CollectionTestServer::server()->collection($orderId);
        # 还款流程
        RepayTestServer::server()->repay($orderId);
        # 退出登录
        $loginTest->testLogout();

        /** 老用户不需要认证 **/

        /** 清除测试数据 **/


    }

    public function testModule()
    {
    }

}
