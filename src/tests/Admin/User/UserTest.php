<?php

use Admin\Models\User\UserBlack;
use Tests\Admin\TestBase;

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/10
 * Time: 21:25
 */
class UserTest extends TestBase
{
    /**
     * 用户列表
     */
    public function testUserList()
    {
        $params = [
            'order_status' => ['user_cancel'],
        ];
        $this->json('GET', '/api/user/index', $params)
            ->getData();
        /*$this->json('GET', '/api/user/index', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);*/
    }

    /**
     * 用户黑名单列表
     */
    public function testUserBlackList()
    {
        $params = [
            'keyword' => '程旭升',
        ];
        $this->json('GET', '/api/user/black_list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])->getData();
    }

    /**
     * 用户反馈列表
     */
    public function testUserFeedBackList()
    {
        $params = [
            'keyword' => '程旭升',
        ];
        $this->json('GET', '/api/user/feedback_list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);

        $params['export'] = 1;
        $this->json('GET', '/api/user/feedback_list', $params);
        $this->assertResponseOk();
    }

    /**
     * 用户详情
     */
    public function testUserView()
    {
        $params = [
            'id' => 1,
        ];
        $this->json('GET', '/api/user/view', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

    /**
     * 用户黑名单详情
     */
    public function testUserBlackView()
    {
        $params = [
            'id' => 1,
        ];
        $this->json('GET', '/api/user/black_view', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

    public function testUserAddBlack()
    {
        $params = [
            'id' => 1,
            'remark' => '测试用例',
        ];
        $this->json('POST', '/api/user/add_black', $params)
            ->seeJson(['code' => self::ERROR_CODE]);
        $params['type'] = UserBlack::TPYE_CANNOT_LOGIN;
        $params['black_time'] = \Common\Utils\Data\DateHelper::date();
        $this->json('POST', '/api/user/add_black', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

    public function testUserMoveBlack()
    {
        $params = [
            'id' => 4262,
        ];
        $this->json('GET', '/api/user/move_black', $params)
            ->seeJson(['code' => self::ERROR_CODE])->getData();
    }
}
