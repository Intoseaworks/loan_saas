<?php

namespace Risk\Common\Services\Task;

trait RiskNoticeCurl
{
    /**
     * 发送通知
     * @param $url
     * @param array $data
     * @param int $timeout
     *
     * @return bool|string
     */
    public static function postToJsonCurl($url, array $data = [], $timeout = 60)
    {
        $dataString = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); //不自动输出任何内容到浏览器
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);   //只需要设置一个秒的数量就可以
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        // 设置header头
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataString)
        ]);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}
