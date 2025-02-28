<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/24
 * Time: 15:36
 */

namespace Tests\Api\Order;

use Api\Models\User\UserAuth;
use Tests\Api\TestBase;

class RiskTest extends TestBase
{
    /**
     * 更新认证状态
     */
    public function testAuthTime()
    {
        $params = [
            'authName' => UserAuth::TYPE_TELEPHONE,
            'userId' => 1,
            'authStatus' => UserAuth::AUTH_STATUS_SUCCESS,
            'time' => date('Y-m-d H:i:s'),
        ];
        $this->post('app/risk/auth_time', $params)->seeJson([
            'code' => self::ERROR_CODE,
        ]);
        $params['token'] = 'b7c23032fcd840c2f4ff4a68f0bf72df';
        $this->post('app/risk/auth_time', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

}
