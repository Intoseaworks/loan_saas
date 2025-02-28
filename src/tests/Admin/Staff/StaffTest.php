<?php

use Tests\Admin\TestBase;

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/10
 * Time: 21:25
 */
class StaffTest extends TestBase
{
    /**
     * 添加
     */
    public function testCreate()
    {
        $params = [
            'username' => 'admin' . rand(1000, 9999),
            'nickname' => '测试管理员',
            'password' => '111111',
            'role_ids' => '1',
        ];
        $this->json('POST', '/api/staff/create', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

}