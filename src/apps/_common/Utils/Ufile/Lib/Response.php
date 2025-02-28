<?php
/**
 * Created by IntelliJ IDEA.
 * User: len
 * Date: 2019/11/20
 * Time: 14:00
 * author: soliang
 */

namespace Common\Utils\UFile\Lib;

class Response
{
    public $statusCode;
    public $header;
    public $contentLength;
    public $body;

    public $errRet;
    public $errMsg;
    public $data;

    public function __construct($code, $body, $headers = [])
    {
        $this->statusCode = $code;
        $this->header = $headers;
        $this->body = $body;
        $this->contentLength = strlen($body);

        $this->parseResult();
    }

    public function setHeader(array $header)
    {
        $this->header = $header;
    }

    public function isSuccess()
    {
        if (floor($this->statusCode / 100) != 2) {
            return false;
        }
        return true;
    }

    public function isFailed()
    {
        return !$this->isSuccess();
    }

    public function getErrMsg()
    {
        return $this->errMsg;
    }

    protected function parseError()
    {
        $r = 0;
        $m = '';
        $mp = json_decode($this->body);
        if (isset($mp->{'ErrRet'})) $r = $mp->{'ErrRet'};
        if (isset($mp->{'ErrMsg'})) $m = $mp->{'ErrMsg'};
        return [$r, $m];
    }

    protected function addData($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function getData($key = null)
    {
        if (is_null($key)) {
            return $this->data;
        }

        return $this->data[$key] ?? '';
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

    public function parseResult()
    {
        if (!$this->isSuccess()) {
            list($this->errRet, $this->errMsg) = $this->parseError();
        }
        if ($this->body && $data = json_decode($this->body, true)) {
            $this->data = $data;
        }

        $etag = $this->getHeader('ETag');
        if ($etag != '') {
            $this->addData('ETag', $etag);
        }

        return $this;
    }
}
