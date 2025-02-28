<?php
/**
 * Created by IntelliJ IDEA.
 * User: len
 * Date: 2019/11/19
 * Time: 19:34
 * author: soliang
 */

namespace Common\Utils\UFile\Lib;

class RequestEntity
{
    private $actionType;
    private $parseUrl;
    private $rawQuery = [];
    private $header = [];
    private $body;
    private $method;
    private $bucket;
    private $key;
    private $timeout;
    private $ua;

    public function __construct($actionType, $host, $body, $bucket, $key, array $query = [])
    {
        $this->actionType = $actionType;
        $this->parseUrl = $this->generateParseUrl($host, $key);
        if ($query) {
            $this->rawQuery = $query;
        }
        $this->header = array();
        $this->body = $body;
        $this->bucket = $bucket;
        $this->key = $key;
        $this->timeout = 60;

        $this->method = ActionType::getMethod($actionType);
        $this->ua = $this->_getUserAgent();
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getBucket()
    {
        return $this->bucket;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getUa()
    {
        return $this->ua;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function getRawQuery()
    {
        return $this->rawQuery;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getParseUrl()
    {
        return $this->parseUrl;
    }

    public function generateParseUrl($host, $key)
    {
        $path = ActionType::getUrlPath($this->actionType, $key);
        return [
            'host' => $host,
            'path' => $path,
        ];
    }

    public function getHeader($key = null)
    {
        if (is_null($key)) {
            return $this->header;
        }

        $val = @$this->header[$key];
        if (isset($val)) {
            if (is_array($val)) {
                return $val[0];
            }
            return $val;
        } else {
            return '';
        }
    }

    public function setHeader($key, $value)
    {
        $this->header[$key] = $value;
    }

    public function appendHeader(array $header)
    {
        $this->header = array_merge($this->header, $header);
    }

    private function _getUserAgent()
    {
        $sdkInfo = "UCloudPHP/1.0.8";

        $systemInfo = php_uname("s");
        $machineInfo = php_uname("m");

        $envInfo = "($systemInfo/$machineInfo)";

        $phpVer = phpversion();

        $ua = "$sdkInfo $envInfo PHP/$phpVer";
        return $ua;
    }
}
