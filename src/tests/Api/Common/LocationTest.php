<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/5
 * Time: 14:25
 */

namespace Tests\Api\Common;

use Tests\Api\TestBase;

class LocationTest extends TestBase
{
    public function testDistrictCode()
    {
        $this->get('app/district-code')->seeJson(['code' => self::SUCCESS_CODE])->getData();
    }

}
