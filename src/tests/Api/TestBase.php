<?php

namespace Tests\Api;

use Api\Models\User\User;
use Api\Services\Login\LoginServer;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;
use JMD\App\lumen\Utils;
use JMD\Libs\Services\BaseRequest;
use JMD\Utils\SignHelper;
use Tests\TestCase;

class TestBase extends TestCase
{
    protected $appInfo = [];

    protected $appKey = 'APP5E7DE05652DB8517';
    protected $clientId = '000d5db9068d5f1e';

    public $userTokenKey = 'test:login:common_test_token:';
    public $testRedisKey = 'test:api:test_data:';
    public $expireTime = 3600;
//    public $token = '';
    public $user;


    public function loginBak($testUserId = 1)
    {
        $key = $this->userTokenKey . $testUserId;
        $token = Redis::get($key);

        if (!$token) {
            $user = User::model()->getOne($testUserId);

            $this->assertNotNull($user, '未找到测试用户');

            $loginResult = LoginServer::server()->login($user);

            $token = $loginResult['token'];
            Redis::set($key, $token, 'EX', $this->expireTime);
        }

        return $this->user = $user;
    }

    public function logined($testUserId = 1)
    {
        return $user = User::model()->getOne($testUserId);
    }


    /**
     *
     * @param $uri
     * @param $params
     * @param bool $decode
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    protected function postSend($uri, $params, $decode = true)
    {
        $result = $this->clientPost($uri, $params);
        return $decode ? json_decode($result, true) : $result;
    }

    private function clientPost($uri, $params)
    {
        $client = new Client();
        $options = [
            'form_params' => $params,
        ];
        if (strpos($uri, 'https://') === 0) {
            $options['verify'] = false;
        }
        $res = $client->post($uri, $options);

        return (string)$res->getBody();
    }

    protected function setCache($key, $value, $ex = 3600)
    {
        $value = json_encode($value);
        $key = $this->getCacheKey($key);
        return Redis::set($key, $value, 'EX', $ex);
    }

    protected function getCache($key, $assertNull = true, $ex = 3600)
    {
        $redisKey = $this->getCacheKey($key);
        $data = Redis::get($redisKey);
        Redis::EXPIRE($redisKey, $ex);

        $assertNull && $this->assertNotNull($data, $key . '缓存为空');

        return json_decode($data, true);
    }

    protected function getCacheKey($key)
    {
        $key = $this->testRedisKey . static::class . '_' . $key;

        return $key;
    }

    public function sign(&$params)
    {
        $this->appInfo = Utils::getParam(BaseRequest::CONFIG_NAME);

        $params['app_key'] = $this->appInfo['app_key'];
        $apiSecretKey = $this->appInfo['app_secret_key'];
        $params['sign'] = SignHelper::sign($params, $apiSecretKey);
    }

    public function get($uri, array $data = [], array $headers = [])
    {
        $headers = array_merge($headers, [
            'app-key' => $this->appKey,
            'client_id' => $this->clientId
        ]);
        return parent::get($uri, $data, $headers);
    }

    public function post($uri, array $data = [], array $headers = [])
    {
        $headers = array_merge($headers, [
            'app-key' => $this->appKey,
            'client_id' => $this->clientId
        ]);
        return parent::post($uri, $data, $headers);
    }
}
