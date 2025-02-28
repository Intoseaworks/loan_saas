<?php

namespace Tests\Admin\OperateData;

use Tests\Admin\TestBase;


class PostLoanTest extends TestBase
{
    /**
     * 每日收支分析列表
     */
    public function testList()
    {
        $params = [
            'quality' => 1
        ];
        $this->json('GET', '/api/post-loan/list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])->getData();

        $params['export'] = 1;
        $this->json('GET', '/api/post-loan/list', $params);
        $this->assertResponseOk();
    }
}
