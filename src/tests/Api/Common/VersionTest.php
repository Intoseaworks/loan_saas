<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/5
 * Time: 14:25
 */

namespace Tests\Api\Common;

use Tests\Api\TestBase;

class VersionTest extends TestBase
{
    public function testConfig()
    {
        $params = ['platform' => 'android'];
        $this->get('app/version', $params)->seeJson(['code' => self::SUCCESS_CODE])->getData();
    }

}
