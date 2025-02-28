<?php
/**
 * Created by PhpStorm.
 * User: zy
 * Date: 20-10-31
 * Time: 下午4:44
 */

namespace Common\Libraries\PayChannel\_common;

abstract class BasisAbstract
{
    use BasisVariable;

    /**
     * @var object|array 主订单
     */
    public $trade;

    public $ext;

    /**
     * @var array
     */
    public $extParams;

    protected $config;

    public function __construct($order = null, $extParams = null, $configName = null)
    {
        $this->trade = $order;

        $this->ext = $extParams;

    }

    /**
     * 写入日志
     * @param array $logInfo
     * @param string $fileName
     * @param string $path
     */
    protected function _writeLog($logInfo = [], $fileName, $path = '')
    {
        $path = empty($path) ? storage_path('logs') : $path;
        $file = $path . '/' . $fileName . date('_Y_m_d') . '.log';

        $logInfo['time'] = date('Y-m-d H:i:s');

        @file_put_contents($file, json_encode($logInfo) . PHP_EOL, FILE_APPEND);
    }

    /**
     * 发起curl请求
     * @param string $url 请求地址
     * @param mixed $data 请求数据
     * @param int $timeout 超时时间
     * @param bool $ssl 是否ssl
     * @param array $header
     * @param bool $fieldStr
     * @param int $timeout
     * @param string $request
     * @return mixed
     */
    public function send($url, $data, $header = [], $fieldStr = false, $timeout = 30, $ssl = false, $request = 'POST')
    {

        if ($fieldStr === true) {
            $data = http_build_query($data, null, '&');
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $ssl);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);
        // 结果处理
        $decodeResult = json_decode($result, true);
        if (!empty($decodeResult)) {
            return $decodeResult;
        }

        return $result;
    }
}
