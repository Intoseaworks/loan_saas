<?php

namespace Common\Services\Didio;

use Api\Services\OrderAgreement\OrderAgreementServer;
use Common\Models\Third\ThirdPartyLog;
use Common\Services\BaseService;
use Common\Utils\Email\EmailHelper;
use Common\Utils\Host\HostHelper;
use Common\Utils\Upload\FileHelper;

class DigioServer extends BaseService
{
    public static $domain = 'https://api.digio.in';
    public static $domainApp = 'https://app.digio.in';
    public static $port = '';
    public static $clientId = 'AI8DCUSJ2HYCI84K6DH488DBS23F6PWO';
    public static $clientSecret = 'YG4QVHM3HYNXSL4N5AK78OPV89F9Q486';

    public static $redirect_url = '/app/callback/digio/sign-callback';

    /** 上传pdf */
    public $uploadPdfUrl = '/v2/client/document/uploadpdf';

    public $userId;
    /**
     * @var ThirdPartyLog
     */
    public $thirdPartyLog;

    public function __construct($userId = null)
    {
        $this->userId = $userId;
    }

    /**
     * 获取签名 url
     * @param $docId
     * @param $identifier
     * @return string
     */
    public function getUrl($docId, $identifier)
    {
        $domain = self::$domainApp;
        $redirectUrl = HostHelper::getDomain() . str_start(self::$redirect_url, '/');
        $digioUrl = "{$domain}/#/gateway/login/{$docId}/vI3atY/{$identifier}?redirect_url={$redirectUrl}";
        return $digioUrl;
    }

    /**
     * 上传合同 pdf
     * @param $identifier
     * @param $localFilePath
     * @param $orderId
     * @param $name
     * @return bool|mixed
     */
    public function uploadContractPdf($identifier, $localFilePath, $orderId, $name)
    {
        $fileName = $identifier . '-' . $orderId . ".pdf";
        $fileData = FileHelper::base64EncodeFile($localFilePath);

        $header = self::getHeader();
        $postData = [
            'signers' => [
                [
                    'identifier' => $identifier,
                    'name' => $name,
                ],
            ],
            "file_name" => $fileName,
            "file_data" => $fileData
        ];
        $recordRequestData = $postData;
        array_forget($recordRequestData, 'file_data');

        $result = $this->execute($this->uploadPdfUrl, $postData, $header, ThirdPartyLog::NAME_CONCRACT_SIGN, $recordRequestData);

        if ($result && !isset($result['id'])) {
            $this->thirdPartyLog->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_SUCCESS, $result, '', '返回参数缺少id');
            $result = false;
        }

        if (!$result) {
            EmailHelper::send([
                'userId' => $this->userId,
                'orderId' => $orderId,
                'data' => $result,
            ], '【失败】Digio合同上传失败', 'wangshaliang@jiumiaodai.com');
        }

        return $result;
    }

    /**
     * 下载(拉取)签名后的 pdf 上传至oss
     * @param $docId
     * @return mixed
     */
    public function downloadContractPdf($docId)
    {
        $url = $this->getRequestUrl('/v2/client/document/download?document_id=' . $docId);
        $header = self::getHeader();
        $returnContent = $this->httpGetData($url, $header);

        $fileName = $docId . '.pdf';
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

    protected function getHeader()
    {
        $basic = base64_encode(self::$clientId . ':' . self::$clientSecret);
        $header = [
            "authorization: Basic " . $basic,
            'content-type: application/json',
        ];
        return $header;
    }

    protected function execute($url, $params, $header = [], $requestName = ThirdPartyLog::NAME_CONCRACT_SIGN, $recordRequestData = [])
    {
        $requestParams = is_array($params) ? json_encode($params) : $params;

        /** 请求监控log */
        $thirdPartyLog = new ThirdPartyLog();
        $requestData = $recordRequestData ?: $params;
        $thirdPartyLog->createByRequest($requestName, $requestData, $url, '', '', $this->userId);
        $this->thirdPartyLog = $thirdPartyLog;

        list($returnCode, $result) = $this->httpPostData($this->getRequestUrl($url), $requestParams, $header);
        if ($returnCode != 200) {
            $thirdPartyLog->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_FAIL, $result, '', '返回code异常：' . $returnCode);
            return false;
        }

        $data = json_decode($result, true);
        $thirdPartyLog->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_SUCCESS, $result, $data['id']);

        return $data;
    }

    private function httpPostData($url, $dataString, $header)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return array($return_code, $return_content);
    }

    private function httpGetData($url, $header)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.111 Safari/537.36');
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }

    protected function getRequestUrl($url)
    {
        $port = self::$port ? str_start(self::$port, ':') : '';
        $url = str_start($url, '/');
        return self::$domain . $port . $url;
    }
}
