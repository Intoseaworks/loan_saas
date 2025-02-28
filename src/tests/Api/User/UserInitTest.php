<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/20
 * Time: 11:28
 */

namespace Tests\Api\User;

use Tests\Api\TestBase;

class UserInitTest extends TestBase
{
    public function testLastLoan($userId = 68470)
    {
        /** 无token/token过期 */
        $this->post('app/user-init/last-loan')->seeJson([
            'code' => self::AUTHORIZATION_CODE
        ]);
        $this->post('app/user-init/last-loan', ['token' => $userId])->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    public function testUserInitIndex($userId = 68470)
    {
        $this->post('app/user-init/index', ['token' => $userId])->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }

    public function testStepThree($userId = 68557)
    {
        $this->post('app/user-init/step-three', [
            'token' => $userId,
            'employment_type' => 'temporary worker',
            'industry' => 'IT',
            'company' => 'IT',
            'work_phone' => 9876543210,
        ])->seeJson([
            'code' => self::SUCCESS_CODE
        ])->getData();
    }
}
