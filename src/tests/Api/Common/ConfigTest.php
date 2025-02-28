<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/5
 * Time: 14:25
 */

namespace Tests\Api\Common;

use Tests\Api\TestBase;

class ConfigTest extends TestBase
{
    public function testConfig()
    {
        $this->get('app/config')->seeJson(['code' => self::SUCCESS_CODE])->getData();
    }

    public function testIndex()
    {
        $params = [];
        $this->get('app/config', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

    public function testConfigOption()
    {
        $this->seeGetRequest('app/config/option', [], false);
    }
}
