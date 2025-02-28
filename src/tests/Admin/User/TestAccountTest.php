<?php

use Tests\Admin\TestBase;

class TestAccountTest extends TestBase
{
    /**
     * 测试用户列表
     */
    public function testTestAccountList()
    {
        $params = [
        ];
        $this->json('GET', '/api/test-account/list', $params)
            ->getData();
    }

    /**
     * 根据keyword 查找用户
     */
    public function testFindUser()
    {
        $params = [
            'keyword' => '18514672336',
        ];
        $this->json('GET', 'api/test-account/find-user', $params)
            ->getData();
    }

    /**
     * 添加测试用户
     */
    public function testAdd()
    {
        $params = [
            'user_id' => '5',
        ];
        $this->json('POST', 'api/test-account/add', $params)
            ->getData();
    }

    /**
     * 查看测试用户详情
     */
    public function testDetail()
    {
        $params = [
            'id' => '2',
        ];
        $this->json('get', 'api/test-account/detail', $params)
            ->getData();
    }

    public function testControlPanel()
    {
        $params = [
            'id' => '2',
            'panel' => 'collection_statistics_clear',
        ];
        $this->json('get', 'api/test-account/control-panel', $params)
            ->getData();
    }
}
