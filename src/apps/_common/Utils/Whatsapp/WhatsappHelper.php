<?php

namespace Common\Utils\Whatsapp;

class WhatsappHelper {

    private $_appKey = "128c8a2f5ed84cefb3881be03c18e8e2";
    private $_security = "89ee373a42c84e4db6122cd2b44d5816";
    private $_url = "https://warisk.whatsappmarketing.cn/api/wa_scan/risk_control";
    private $_callback = "https://saas.urupee.in/app/callback/whatsapp";
    //private $_callback = "http://120.78.230.66/app/callback/whatsapp";

    public function sign($postData) {
        $signParam = "appKey={$this->_appKey}&callbackUrl={$this->_callback}&orderId={$postData['orderId']}&timestamp={$postData['timestamp']}&security={$this->_security}";
        return strtoupper(md5($signParam));
    }

    function getUrlQuery($arrayQuery) {
        $tmp = array();
        foreach ($arrayQuery as $k => $param) {
            $tmp[] = $k . '=' . $param;
        }
        $params = implode('&', $tmp);
        return $params;
    }

    public function post($postData) {

        //$postData = ["8613681111385"];

        $param = [
            'appKey' => $this->_appKey,
            'callbackUrl' => $this->_callback,
            'orderId' => uniqid()
        ];
        $timestamp = time()*1000;
        $headerData = [
            "content-type: application/json;charset=UTF-8",
            "timestamp: " . $timestamp,
            "sign: " . $this->sign(["timestamp"=>$timestamp, "orderId"=>$param['orderId']])
        ];
        $ch = curl_init();
        $url = $this->_url . "?" . $this->getUrlQuery($param);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
        $res = curl_exec($ch);
        //curl_error($ch);
        return $res;
    }

}
