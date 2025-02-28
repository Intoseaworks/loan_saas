<?php
/**
 * Created by IntelliJ IDEA.
 * User: len
 * Date: 2019/11/19
 * Time: 16:52
 * author: soliang
 */

namespace Common\Utils\UFile\Lib;

class Auth
{
    const NO_AUTH_CHECK = 0;
    const HEAD_FIELD_CHECK = 1;
    const QUERY_STRING_CHECK = 2;

    private $publicKey;
    private $privateKey;

    public function __construct($publicKey, $privateKey)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    public function sign($data, $type)
    {
        $sign = base64_encode(hash_hmac('sha1', $data, $this->privateKey, true));

        if ($type == self::HEAD_FIELD_CHECK) {
            return "UCloud " . $this->publicKey . ":" . $sign;
        }

        return $sign;
    }

    public function signRequest(RequestEntity $request, $type = self::HEAD_FIELD_CHECK)
    {
        $data = [
            strtoupper($request->getMethod()),
            $request->getHeader('Content-MD5'),
            $request->getHeader('Content-Type'),
        ];

        if ($type === self::HEAD_FIELD_CHECK) {
            $data[] = $request->getHeader('Date');
        } else {
            $expires = $request->getHeader('Expires');
            $data[] = $expires ?? '';
        }

        $data[] = $this->canonicalizedResource($request->getBucket(), $request->getKey());

        $dataStr = implode("\n", $data);

        return $this->sign($dataStr, $type);
    }

    /**
     * ??? 暂不使用，实例代码中的部分
     * @param $headers
     * @return string
     */
    protected function canonicalizedHeaders($headers)
    {
        $keys = [];
        foreach ($headers as $header) {
            $header = trim($header);
            $arr = explode(':', $header);
            if (count($arr) < 2) continue;
            list($k, $v) = $arr;
            $k = strtolower($k);
            if (strncasecmp($k, "x-ucloud") === 0) {
                $keys[] = $k;
            }
        }

        $c = '';
        sort($keys, SORT_STRING);
        foreach ($keys as $k) {
            $c .= $k . ":" . trim($headers[$v], " ") . "\n";
        }
        return $c;
    }

    public function canonicalizedResource($bucket, $key)
    {
        return "/" . $bucket . "/" . $key;
    }
}
