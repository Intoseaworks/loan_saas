<?php

namespace Common\Libraries\PayChannel\Paymob;

use Common\Utils\Helper;
use Illuminate\Support\Facades\Cache;
use Yunhan\Utils\Env;

class PaymobBase {

    use Helper;

    const BANK_CODE = [
        'Ahli United Bank' => 'AUB',
        'Citi Bank N.A. Egypt' => 'CITI',
        'MIDBANK' => 'MIDB',
        'Banque Du Caire' => 'BDC',
        'HSBC Bank Egypt S.A.E' => 'HSBC',
        'Credit Agricole Egypt S.A.E' => 'CAE',
        'Egyptian Gulf Bank' => 'EGB',
        'The United Bank' => 'UB',
        'Qatar National Bank Alahli' => 'QNB',
        'Arab Bank PLC' => 'ARAB',
        'Emirates National Bank of Dubai' => 'ENBD',
        'Al Ahli Bank of Kuwait – Egypt' => 'ABK',
        'National Bank of Kuwait – Egypt' => 'NBK',
        'Arab Banking Corporation - Egypt S.A.E' => 'ABC',
        'First Abu Dhabi Bank' => 'FAB',
        'Abu Dhabi Islamic Bank – Egypt' => 'ADIB',
        'Commercial International Bank - Egypt S.A.E' => 'CIB',
        'Housing And Development Bank' => 'HDB',
        'Banque Misr' => 'MISR',
        'Arab African International Bank' => 'AAIB',
        'Egyptian Arab Land Bank' => 'EALB',
        'Export Development Bank of Egypt' => 'EDBE',
        'Faisal Islamic Bank of Egypt' => 'FAIB',
        'Blom Bank' => 'BLOM',
        'Abu Dhabi Commercial Bank – Egypt' => 'ADCB',
        'Alex Bank Egypt' => 'BOA',
        'Societe Arabe Internationale De Banque' => 'SAIB',
        'National Bank of Egypt' => 'NBE',
        'Al Baraka Bank Egypt B.S.C.' => 'ABRK',
        'Egypt Post' => 'POST',
        'Nasser Social Bank' => 'NSB',
        'Industrial Development Bank' => 'IDB',
        'Suez Canal Bank' => 'SCB',
        'Mashreq Bank' => 'MASH',
        'Arab Investment Bank' => 'AIB',
        'Audi Bank' => 'AUDI',
        'General Authority For Supply Commodities' => 'GASC',
        'Arab International Bank' => 'ARIB',
        'Agricultural Bank of Egypt' => 'PDAC',
        'National Bank of Greece' => 'NBG',
        'Central Bank Of Egypt' => 'CBE',
        'ATTIJARIWAFA BANK Egypt' => 'BBE',
    ];

    protected function getConfig() {
        if (Env::isProd()) {
            return json_decode(env('PAYMOB_PROD'), true);
        }
        return json_decode(env('PAYMOB_TEST'), true);
    }

    protected function _writeLog($logInfo = [], $fileName, $path = '') {
        $path = empty($path) ? storage_path('logs') : $path;
        $file = $path . '/' . $fileName . date('_Y_m_d') . '.log';

        $logInfo['time'] = date('Y-m-d H:i:s');

        @file_put_contents($file, json_encode($logInfo) . PHP_EOL, FILE_APPEND);
    }

    public function post(Array $data, $url, $userpwd = null, $header = null) {
        if (!$header) {
            $header = ["Content-Type: application/x-www-form-urlencoded"];
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($userpwd) {
            curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
        }
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if ($userpwd) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        }
        $response = curl_exec($ch);

        curl_close($ch);
        return json_decode($response, true);
    }

    public function authToken() {
        $config = $this->getConfig();
        $key = "PaymobToken-{$config['client_id']}";
        if (Cache::has($key)) {
            $token = Cache::get($key);
        } else {
            $request = [
                "grant_type" => 'password',
                "username" => $config['username'],
                "password" => $config['password'],
            ];
            $response = $this->post($request, $config['url'] . "/o/token/", $config['client_id'] . ":" . $config['client_secret']);
            $token = $response['access_token'];
            $ttl = 50; # token有效期1小时
            Cache::set($key, $token, $ttl);
        }
        $header = [
            'Content-Type: application/json',
            "Authorization: Bearer {$token}"
        ];
        return $header;
    }

}
