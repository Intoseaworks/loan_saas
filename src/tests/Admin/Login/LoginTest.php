<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/5
 * Time: 14:25
 */

namespace Tests\Admin\Approve;

use Tests\Admin\TestBase;

class LoginTest extends TestBase
{

    /**
     * 账密登录
     *
     * @return array
     */
    public function testPwdLogin()
    {
        $params = [
            'username' => 'admintest',
            'password' => '123456',
            'test' => 'test',
        ];
        $this->json('POST', '/api/login/pwd_login', $params)->seeJson()->getData();
    }

    /**
     * 钉钉登录配置接口
     *
     * @return mixed
     */
    public function testDingLoginView()
    {
        $params = [];
        $this->json('GET', '/api/login/ding_login', $params)->seeJson()->getData();
    }

}