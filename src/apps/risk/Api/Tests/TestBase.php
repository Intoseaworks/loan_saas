<?php

namespace Risk\Api\Tests;

use JMD\Utils\SignHelper;
use Tests\TestCase;

class TestBase extends TestCase
{
    protected $appInfo = [
        'app_key' => 'xrzLC4931smVZ',
//        'app_key' => '',
        'secretKey' => '1ba784b8f1a40d4381eaeb37350971e1',
//        'secretKey' => '',
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
