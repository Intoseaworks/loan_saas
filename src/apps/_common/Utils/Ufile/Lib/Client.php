<?php
/**
 * Created by IntelliJ IDEA.
 * User: len
 * Date: 2019/11/19
 * Time: 20:10
 * author: soliang
 */

namespace Common\Utils\UFile\Lib;

use Common\Utils\UFile\UFileException;

class Client
{
    private $auth;
    /**
     * @var RequestEntity
     */
    private $request;

    public function __construct(Auth $auth, RequestEntity $request = null)
    {
        $this->auth = $auth;
        $this->request = $request;
    }

    public static function instance(Auth $auth)
    {
        $params = func_get_args();
        if ($params) {
            return new static(...$params);
        } else {
            return new static($auth);
        }
    }

    public function setRequest(RequestEntity $request)
    {
        $this->request = $request;
    }

    /**
     * @return Response
     * @throws UFileException
     */
    public function execSign()
    {
        $token = $this->auth->SignRequest($this->request);
        $this->request->setHeader('Authorization', $token);

        $res = $this->exec();

        return $res;
    }

    /**
     * @return Response
     * @throws UFileException
     */
    public function exec()
    {
        $ch = curl_init();
        $url = $this->request->getParseUrl();

        $options = [
            CURLOPT_USERAGENT => $this->request->getUa(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false,
            CURLOPT_CUSTOMREQUEST => $this->request->getMethod(),
            CURLOPT_TIMEOUT => $this->request->getTimeout(),
            CURLOPT_CONNECTTIMEOUT => 10,
        ];

        if ($rawQuery = $this->request->getRawQuery()) {
            $options[CURLOPT_URL] = $url['host'] . "/" . $url['path'] . "?" . http_build_query($rawQuery);
        } else {
            $options[CURLOPT_URL] = $url['host'] . "/" . $url['path'];
        }

        $httpHeader = $this->request->getHeader();
        if (!empty($httpHeader)) {
            $header = array();
            foreach ($httpHeader as $key => $parsedUrlValue) {
                $header[] = "$key: $parsedUrlValue";
            }
            $options[CURLOPT_HTTPHEADER] = $header;
        }

        $body = $this->request->getBody();
        if (!empty($body)) {
            $options[CURLOPT_POSTFIELDS] = $body;
        } else {
            $options[CURLOPT_POSTFIELDS] = "";
        }

        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $ret = curl_errno($ch);

        if ($ret !== 0) {
            throw new UFileException('CURL error:' . curl_error($ch));
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        $responseArray = explode("\r\n\r\n", $result);
        $responseArraySize = sizeof($responseArray);
        $headerString = $responseArray[$responseArraySize - 2];
        $respBody = $responseArray[$responseArraySize - 1];

        $headers = $this->parseHeaders($headerString);

        $response = new Response($code, $respBody, $headers);

        return $response;
    }

    protected function parseHeaders($headerString)
    {

        $headers = explode("\r\n", $headerString);
        $resHeaders = [];
        foreach ($headers as $header) {
            if (strstr($header, ":")) {
                $header = trim($header);
                list($k, $v) = explode(":", $header);
                $resHeaders[$k] = trim($v);
            }
        }
        return $resHeaders;
    }
}
