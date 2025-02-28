<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/24
 * Time: 15:36
 */

namespace Tests\Api\Order;

use Tests\Api\TestBase;

class AuthTest extends TestBase
{

    /**
     * 认证列表
     */
    public function testFaqIndex()
    {
        $params = [
            'token' => $this->getToken(),
        ];
        $this->get('app/auth/index', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])//->getData()
        ;
    }

}
