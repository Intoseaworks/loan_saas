<?php

namespace Common\Utils\Riskcloud;

use Common\Utils\Helper;
use JMD\Libs\Services\DataFormat;
use Common\Utils\DingDing\DingHelper;

class RiskcloudHelper {

    use Helper;

    const CARD_OCR = "cardOcr";
    const PAN_VERIFY = "panVerify";
    const AADHAAR_VERIFY = "aadhaarVerify";
    const SEARCH_CREDIT = "searchExperainCredit";
    const SERVICE_TYPE = [
        self::PAN_VERIFY => "PAN_VERIFY",
        self::AADHAAR_VERIFY => "AADHAAR_VERIFY",
        self::SEARCH_CREDIT => "SEARCH_CREDIT",
    ];
    const SUCCESS_CODE = ["2000", "3000", "4000", "6000", "7000", "8000", "9000", "10000", "11000", "12000"];
    const STATUS_CODE_INPROCESS = ["4007", "6003", "8005"]; //需要在次取取结果的。
    const WARNGING_CODE = ["1000", "1001", "1002", "1003", "1004", "1005", "1006", "1007", "1008", "1009", "1010", "1011", "1012", "1013", "1014"];
    const RETRY_MAX_COUNT = 5;//取结果最多次数

    private $_retryCount = 0; //取结果计数器
    private $_skeySec = 2; //请求异步结果间隔时间
    //

    protected $_config = [
        "rupeecash" => [
            "url" => "https://api.riskcloud.in/v3",
            "appId" => "50Z7x0234Ud5147r",
            "secretKey" => "X7N70W99xwx6z68U",
            "callBackUrl" => "http://saas.urupee.in/callback/riskcloud"
        ],
        "urupee" => [
            "url" => "https://api.riskcloud.in/v3",
            "appId" => "50Z7x0234Ud5147r",
            "secretKey" => "X7N70W99xwx6z68U",
            "callBackUrl" => "http://saas.urupee.in/callback/riskcloud"
        ],
    ];
    protected $_env = "rupeecash";
    protected $_callbackFunction = ['panVerify', 'aadhaarVerify', 'searchMultiLoan'];

    public function __construct($env = "rupeecash") {
        $this->_env = $env;
    }

    public function sign($postData, $appSecret) {
        $postData['appSecret'] = $appSecret;
        ksort($postData);

        $temp = [];
        foreach ($postData as $key => $value) {
            if (!is_array($value)) {
                $temp[] = $key;
                $temp[] = $value;
            }
        }
        return sha1(implode("", $temp));
    }

    public function query($reportId, $serviceType) {
        sleep($this->_skeySec);
        $url = $this->_config[$this->_env]['url'] . "/query/" . self::SERVICE_TYPE[$serviceType] . '/' . $reportId;
        $res = $this->post($url, []);
        $res = json_decode($res, true);
        if (in_array($res['data']['errorCode'], self::STATUS_CODE_INPROCESS) && $this->_retryCount < self::RETRY_MAX_COUNT) {
            $this->_retryCount++;
            return $this->query($reportId, $serviceType);
        }
        return $res;
    }

    public function __call($name, $arguments) {
        $url = $this->_config[$this->_env]['url'] . "/" . $name;
        if (in_array($name, $this->_callbackFunction)) {
            $arguments[0]['callbackUrl'] = $this->_config[$this->_env]['callBackUrl'];
        }
        $res = $this->post($url, $arguments[0]);
        $res = json_decode($res, true);
        if ($res['statusCode'] == '0') {
            if (in_array($name, $this->_callbackFunction)) {
                $queryRes = $this->query($res['reportId'], $name);
                $res = $queryRes;
            }
            $res['data']['reportId'] = $res['reportId'];
            if (in_array($res['data']['errorCode'], self::SUCCESS_CODE)) {
                $res = [
                    "code" => DataFormat::OUTPUT_SUCCESS,
                    "msg" => $res['data']['errMessage'],
                    "data" => $res['data']];
                return new DataFormat($res);
            } else {
                $res = [
                    "code" => DataFormat::OUTPUT_ERROR,
                    "msg" => $res['data']['errMessage'],
                    "data" => $res['data']
                ];
                return new DataFormat($res);
            }
        } else {
            DingHelper::notice("闪云错误码:[{$res['statusCode']}][接口名:{$name}][参数---" . json_encode($arguments) . "---]", "错误环境:{$res['statusCode']}" . app()->environment());
            $res = [
                "code" => DataFormat::OUTPUT_ERROR,
                "msg" => "ERROR",
                "data" => json_encode($res)];
            return new DataFormat($res);
        }
    }

    public function checkResult($name, $res) {
        switch ($name) {
            case self::PAN_VERIFY:

                break;
        }
    }

    public function post($url, $postData) {
        $postData['appId'] = $this->_config[$this->_env]['appId'];
        $postData['timestamp'] = time() * 1000;
        $postData['nonce'] = uniqid();
        $postData['sign'] = $this->sign($postData, $this->_config[$this->_env]['secretKey']);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["content-type: application/json;charset=UTF-8"]);
        $res = curl_exec($ch);
        //curl_error($ch);
        return $res;
    }

}
