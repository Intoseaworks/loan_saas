<?php


namespace Api\Tests\Common;


use Api\Tests\TestBase;

class CaptchaTest extends TestBase
{
    public function testSendMail()
    {
        $params = [
            'email' => '124@qq.com',
        ];
        $this->seePostRequest('/app/captcha/action/send-email', $params);
    }
}
