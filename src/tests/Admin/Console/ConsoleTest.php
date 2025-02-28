<?php

namespace Tests\Admin\Console;

use Tests\Admin\TestBase;

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/10
 * Time: 21:25
 */
class ConsoleTest extends TestBase
{
    /**
     * 催收分单
     */
    public function testCollectionAssign()
    {
        $params = [];
        $this->json('GET', '/api/test/console/collection_assign', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

    /**
     * 流转坏账
     */
    public function testFlowCollectionBad()
    {
        $params = [];
        $this->json('GET', '/api/test/console/flow_collection_bad', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

}