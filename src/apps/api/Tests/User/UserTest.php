<?php


namespace Api\Tests\User;


use Api\Tests\TestBase;

class UserTest extends TestBase
{


    public function testUserHome()
    {
        $params = [

        ];

        $this->seeRequest('GET', '/app/user/home', $params);
    }

    public function testUserIdentity()
    {
        $params = [

        ];

        $this->seeRequest('GET', '/app/user/identity', $params);
    }

}
