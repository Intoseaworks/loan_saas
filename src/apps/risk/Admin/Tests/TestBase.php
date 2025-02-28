<?php

namespace Risk\Admin\Tests;

use JMD\Utils\SignHelper;
use Tests\TestCase;

class TestBase extends TestCase
{
    protected $appInfo = [
        'app_key' => 'X0p1jrEG3750hmuBru',
        'secretKey' => '622091b20a9096580e63b2fa78d86e5f',
    ];

    /**
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function sign(&$params)
    {
        $params['app_key'] = $this->appInfo['app_key'];
        $apiSecretKey = $this->appInfo['secretKey'];
        $params['sign'] = SignHelper::sign($params, $apiSecretKey);
        return $params;
    }
}
