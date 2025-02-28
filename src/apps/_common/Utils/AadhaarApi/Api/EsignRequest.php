<?php

namespace Common\Utils\AadhaarApi\Api;

use Api\Services\OrderAgreement\OrderAgreementServer;
use Common\Models\Third\ThirdPartyLog;
use Common\Utils\Curl;

class EsignRequest extends AadhaarRequest
{
    public $signUrl = '/app/contract/sign-page';

    public $responseUrl = '/app/callback/aadhaar/esign-response';

    public function esignInit($userId, $filePath, $userName, $city = '')
    {
        $file = file_get_contents($filePath);
        $pageNum = preg_match_all("/\/Page\W/", $file, $dummy);
        $base64 = base64_encode($file);

        $responseUrl = config('config.api_client_domain') . str_start($this->responseUrl, '/');
        $params = [
            'document' => [
                'data' => $base64,
                'type' => 'pdf',
                'info' => 'Formal signing of the order', // 不能少于10个字符
                'xCoordinate' => '0',
                'yCoordinate' => '0',
                'signPageNumber' => $pageNum,
            ],
            'signerName' => $userName,
            'signerCity' => $city,
            'purpose' => 'loan',
            'version' => '2.1',
            'responseURL' => $responseUrl,
        ];
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(
            ThirdPartyLog::NAME_ESIGN,
            $params,
            Config::getEsignUrl(),
            '',
            '',
            $userId
        );

        return $this->request(http_build_query($params), Config::getEsignUrl(), $thirdPartLogModel);
    }

    /**
     * 构造签约页面html
     * @param $transactionId
     * @param string $fillEmail
     * @param string $fillTelephone
     * @return false|mixed|string
     */
    public function buildSignPage($transactionId, $fillEmail = '', $fillTelephone = '')
    {
        $options = [
            'companyName' => 'test company name',
            'frontTextColor' => 'FFFFFF',
            'backgroundColor' => '2C3E50',
            'logoUrl' => 'https://akta.org/wp-content/uploads/2015/02/sponsorship-fpo.gif',
            'validatePhone' => 'y',
            'validateEmail' => 'y',
            'transactionId' => $transactionId,
            'fillEmail' => $fillEmail,
            'fillTelephone' => $fillTelephone,
        ];

        $htmlContent = file_get_contents(public_path('/api/esign/index.html'));
        $replace = [];
        foreach ($options as $k => $v) {
            $replace["{{{$k}}}"] = $v;
        }
        $htmlContent = str_replace(array_keys($replace), array_values($replace), $htmlContent);

        return $htmlContent;
    }

    public function buildSignUrl($transactionId)
    {
        // 改为安卓接sdk，不使用web sdk，直接返回transactionId
        return $transactionId;

        $params = [
            'transactionId' => $transactionId,
        ];
        $signUrl = config('config.api_client_domain') . str_start($this->signUrl, '/') . '?' . http_build_query($params);
        return $signUrl;
    }

    public function downloadContractPdfByDocId($docId)
    {

    }

    /**
     * 下载已签名的pdf合同
     * @param $url
     * @return string
     */
    public function downloadContractPdf($url)
    {
        $returnContent = Curl::get($url);

        $fileName = uniqid('esign') . rand(100, 999) . '.pdf';
        $path = $this->getDirPath() . $fileName;
        file_put_contents($path, $returnContent);

        $ossFilePath = OrderAgreementServer::server()->uploadToOss($path, $fileName);
        @unlink($path);
        return $ossFilePath;
    }

    public function getDirPath()
    {
        $dir = public_path('tmp/');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }
        return $dir;
    }
}
