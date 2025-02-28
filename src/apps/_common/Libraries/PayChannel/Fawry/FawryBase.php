<?php

namespace Common\Libraries\PayChannel\Fawry;

use Common\Utils\Helper;
use Yunhan\Utils\Env;

class FawryBase {

    use Helper;

    protected function getConfig() {
        if (Env::isProd()) {
            return json_decode(env('FAWRY_PROD'), true);
        }
        return json_decode(env('FAWRY_TEST'), true);
    }

    protected function _writeLog($logInfo = [], $fileName, $path = '') {
        $path = empty($path) ? storage_path('logs') : $path;
        $file = $path . '/' . $fileName . date('_Y_m_d') . '.log';

//        $logInfo['time'] = date('Y-m-d H:i:s');

        @file_put_contents($file, json_encode($logInfo) . PHP_EOL, FILE_APPEND);
    }

    public function post(Array $data, $url, $header = null) {
        $this->_writeLog($data, "fawry_repay_request");
        if($header == null){
            $header = ["content-type: application/json;charset=UTF-8", 'Accept: application/json'];
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        $response = curl_exec($ch);
        $response = json_decode($response, true);
        $this->_writeLog($response, "fawry_repay_request");
        curl_close($ch);
        return $response;
    }

    public function token(){
        $config = $this->getConfig();
        $userIdentifier = $config['userIdentifier'];
        $password  = $config['password'];
        $data = [
            'userIdentifier' => $userIdentifier,
            'password' => $password,

        ];
        $response =$this->post($data, $config['url']. "/user-api/auth/login");
        return $response['token'];
    }
}
