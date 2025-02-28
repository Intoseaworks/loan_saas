<?php

namespace Tests;

use Api\Models\User\User;
use Api\Services\Login\LoginServer;
use Illuminate\Support\Str;
use PHPUnit\Framework\Assert as PHPUnit;

class TestCase extends \Laravel\Lumen\Testing\TestCase
{
    const SUCCESS_CODE = 18000;
    const ERROR_CODE = 13000;
    const AUTHORIZATION_CODE = 1403;

    public static $userId = 163;
    public static $orderId = 1;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    public function setUp()
    {
        parent::setUp();

        // 模拟添加 HTTP_HOST
        $_SERVER['HTTP_HOST'] = 'test.com';
    }

    public function getData($isPrint = true, $original = false)
    {
        $this->assertResponseOk();
        $result = $this->response->content();
        $data = $original ? $result : @json_decode($result, true);
        if ($isPrint) {
            // 使用print_r不能正常打印true false
            var_export($data);
        }

        if (isset($data['data'])) {
            return $data['data'];
        }

        return $data;
    }

    public function seeGetRequest($uri = '', $params = [], $decode = true)
    {
        return $this->seeRequest('GET', $uri, $params, $decode);
    }

    public function seePostRequest($uri = '', $params = [], $decode = true)
    {
        return $this->seeRequest('POST', $uri, $params, $decode);
    }

    public function seeRequest($method = 'GET', $uri = '', $params = [], $decode = true)
    {
        if ($method == 'GET') {
            $test = $this->get($uri, $params, ['app-key' => 'APP5D69EAEFD74DD38']);
        } else {
            $test = $this->post($uri, $params);
        }
        return $test->seeJson(['code' => self::SUCCESS_CODE])->getData(true, !$decode);
    }

    public function dump()
    {
        $content = $this->response->content();
        $data = @json_encode(@json_decode($content), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (empty($data) || $data === 'null') {
            dd($content);
        }
        echo ($data) . PHP_EOL;
        die();
    }

    public function get($uri, array $data = [], array $headers = [])
    {
        return $this->json('GET', $uri, $data, $headers);
    }

    /**
     * json转化成array
     * @param $json
     * @return mixed
     */
    public function decode($json)
    {
        return json_decode($json, true);
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return self::$userId;
    }

    /**
     * @param $userId
     */
    public function setUserId($userId)
    {
        self::$userId = $userId;
    }

    /**
     * @param int $testUserId
     * @return array
     * @throws \Common\Exceptions\ApiException
     */
    public function getToken($testUserId = 0)
    {
        if ($testUserId == 0) {
            $testUserId = self::$userId;
        }
        $user = User::find($testUserId);
        $result = LoginServer::server()->login($user);

        return $result['token'];
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return self::$orderId;
    }

    /**
     * @param $orderId
     */
    public function setOrderId($orderId)
    {
        self::$orderId = $orderId;
    }

    /**
     * Assert that the response contains the given JSON.
     * json_encode 改为256 方便查看
     * @param array $data
     * @param bool $negate
     * @return $this
     */
    protected function seeJsonContains(array $data, $negate = false)
    {
        $method = $negate ? 'assertFalse' : 'assertTrue';

        $actual = json_decode($this->response->getContent(), true);

        if (is_null($actual) || $actual === false) {
            return PHPUnit::fail('Invalid JSON was returned from the route. Perhaps an exception was thrown?');
        }

        $actual = json_encode(array_sort_recursive(
            (array)$actual
        ), 256);

        foreach (array_sort_recursive($data) as $key => $value) {
            $expected = $this->formatToExpectedJson($key, $value);

            PHPUnit::{$method}(
                Str::contains($actual, $expected),
                ($negate ? 'Found unexpected' : 'Unable to find') . " JSON fragment [{$expected}] within [{$actual}]."
            );
        }

        return $this;
    }
}
