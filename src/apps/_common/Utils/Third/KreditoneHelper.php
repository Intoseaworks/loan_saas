<?php

namespace Common\Utils\Third;

use Common\Utils\Helper;
use Yunhan\Utils\Env;

class KreditoneHelper {

    use Helper;

    const PROD_URL = "https://api.kreditone.in";
    const PROD_KEY = "x0pKMEjigBRS55pM46UA+tICTG";
    const PROD_ACCOUNT_ID = "ucash";
    const TEST_URL = "https://api-uat.kreditone.in";
    const TEST_KEY = "key123";
    const TEST_ACCOUNT_ID = "test";

    private $_key = "";
    private $_accountId = "";
    private $_url = "";

    public function __construct() {
        if (Env::isProd()) {
            $this->_key = self::PROD_KEY;
            $this->_accountId = self::PROD_ACCOUNT_ID;
            $this->_url = self::PROD_URL;
        } else {
            $this->_key = self::TEST_KEY;
            $this->_accountId = self::TEST_ACCOUNT_ID;
            $this->_url = self::TEST_URL;
        }
    }

    public function sign($queryId) {
        return hash_hmac('sha256', $queryId, $this->_key);
    }

    public function post($postData, $uri) {
        $headerData = [
            "content-type: application/json;charset=UTF-8",
            "account-id: " . $this->_accountId,
            "x-kreditone-signature: " . $this->sign($postData['queryId'])
        ];
        $ch = curl_init();
        $url = $this->_url . $uri;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true);
    }

}
