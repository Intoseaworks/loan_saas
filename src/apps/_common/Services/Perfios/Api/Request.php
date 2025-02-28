<?php

namespace Common\Services\Perfios\Api;

use Risk\Common\Helper\CurlHelper;
use SimpleXMLElement;
use Yunhan\Utils\Env;

class Request
{
    /**
     * @var string
     */
    const TRANSACTION_COMPLETE = 'TRANSACTION_COMPLETE';

    /**
     * @var string
     */
    const STATUS_COMPLETE = 'COMPLETED';

    /**
     * @param $xml
     * @return string
     */
    public function generateSign($xml)
    {
        openssl_private_encrypt(bin2hex(sha1($xml, true)), $crypted, $this->getPrivateKey());
        return bin2hex($crypted);
    }

    /**
     * @param $data
     * @return string
     */
    public function startProcess($data)
    {
        $template = <<<FORM
<html>
	<body onload='document.autoform.submit();'>
		<form name='autoform' method='post' action='%s'>
			<input type='hidden' name='payload' value='%s'>
			<input type='hidden' name='signature' value='%s'>
		</form>
	</body>
</html>
FORM;

        !empty($data['yearMonthFrom']) ? $params['yearMonthFrom'] = $data['yearMonthFrom'] : '';
        !empty($data['yearMonthTo']) ? $params['yearMonthTo'] = $data['yearMonthTo'] : '';
        !empty($data['returnUrl']) ? $params['returnUrl'] = $data['returnUrl'] : '';

        $params['destination'] = $data['destination'];
        $params['transactionCompleteCallbackUrl'] = Config::getCallbackUrl();
        $params['txnId'] = $data['userId'];
        $params['emailId'] = $this->getEmailId(Config::getEmail());
        $this->getBaseParams($params);
        $xml = $this->getXmlparams($params);
        $sign = $this->generateSign($xml);

        return sprintf($template, Config::getStartProcessUrl(), $xml, $sign);
    }

    /**
     * @param $params
     */
    public function txnStatus($data)
    {
        $params = [
            'txnId' => $data['userId'],
        ];
        return $this->request($params, Config::getTxnStatusUrl());
    }

    /**
     * @param $data
     * @return mixed
     */
    public function retrieve($data)
    {
        $params = [
            'perfiosTransactionId' => $data['actionId'],
            'reportType' => 'xml',
            'txnId' => $data['userId'],
        ];
        return $this->request($params, Config::getRetrieveUrl());
    }

    /**
     * @param $params
     */
    protected function getBaseParams(&$params)
    {
        $params['apiVersion'] = '2.1';
        $params['vendorId'] = Config::getVendorId();
    }

    /**
     * @param $params
     * @return mixed|string|string[]|null
     */
    protected function getXmlparams($params)
    {
        $simpleXMLElement = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><payload></payload>');
        $this->array2Xml($params, $simpleXMLElement);
        $xml = $simpleXMLElement->asXML();
        $xml = str_replace("<?xml version=\"1.0\" encoding=\"UTF-8\"?>", '', $xml);
        $xml = preg_replace('#[\r\n,\r,\n]#', '', $xml);

        return $xml;
    }

    /**
     * @param $data
     * @param SimpleXMLElement $simpleXMLElement
     */
    protected function array2Xml($data, SimpleXMLElement $simpleXMLElement)
    {
        foreach ($data as $key => $value) {
            if (strpos($key, '*') !== false && preg_match('#\d+#', explode('*', $key)[1])) {
                $key = explode('*', $key)[0];
            }
            if (is_array($value)) {
                $subnode = $simpleXMLElement->addChild($key);
                $this->array2Xml($value, $subnode);
            } else {
                $simpleXMLElement->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    /**
     * @param $xml
     * @return mixed
     */
    protected function xml2Array($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

    /**
     * @return false|string
     */
    protected function getPrivateKey()
    {
        if (!Env::isProd()) {
            return file_get_contents(__DIR__ . '/keys/private-prod.key');
        }

        return file_get_contents(__DIR__ . '/keys/private-dev.key');
    }

    /**
     * @return false|string
     */
    protected function getPublicKey()
    {
        return file_get_contents(__DIR__ . '/keys/public-prod.key');

        return file_get_contents(__DIR__ . '/keys/public-dev.key');
    }

    /**
     * @param $email
     * @return mixed
     */
    protected function getEmailId($email)
    {
        openssl_public_encrypt($email, $crypted, $this->getPublicKey());
        return bin2hex($crypted);
    }

    /**
     * @param $data
     * @param $url
     * @return mixed
     */
    protected function request($data, $url)
    {
        $this->getBaseParams($data);
        $xml = $this->getXmlparams($data);
        $sign = $this->generateSign($xml);
        $queryparams = [
            'payload' => $xml,
            'signature' => $sign,
        ];
        $header = [
            "Content-Type:application/x-www-form-urlencoded"
        ];
        $result = CurlHelper::post(http_build_query($queryparams), $url, $header);
        return $this->xml2Array($result);
    }

}
