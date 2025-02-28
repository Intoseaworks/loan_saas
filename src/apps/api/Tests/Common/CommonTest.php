<?php

namespace Api\Tests\Common;

use Api\Services\Common\VersionServer;
use Api\Tests\TestBase;

class CommonTest extends TestBase
{
    public function testGetVersion()
    {
        $params = [
            'platform' => VersionServer::PLATFORM_ANDROID,
        ];
        $this->seeGetRequest('/app/version', $params);
    }

    public function testConfig()
    {
        $this->seeGetRequest('/app/config');
    }

    public function testUserInfo()
    {
        $this->seeGetRequest('/app/config/user-info-config');
    }
}
