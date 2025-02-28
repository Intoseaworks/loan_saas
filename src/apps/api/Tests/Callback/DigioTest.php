<?php

namespace Api\Tests\Callback;

use Api\Tests\TestBase;

class DigioTest extends TestBase
{
    public function testSignCallback()
    {
        $params = [
        ];
        $this->seePostRequest('/app/callback/digio/sign-callback', $params);
    }

    public function testReturnSuccess()
    {
        $this->get('/app/callback/digio/success', []);
        $result = $this->response->content();
        dd($result);
    }

    public function testReturnFail()
    {
        $this->get('/app/callback/digio/fail', []);
        $result = $this->response->content();
        dd($result);
    }
}
