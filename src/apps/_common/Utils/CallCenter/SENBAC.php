<?php

namespace Common\Utils\CallCenter;

use Common\Utils\Curl;

class SENBAC {

    const CONFIG = [
        "dev" => [
            "1" => [ #商铺ID
                "url" => "http://202.124.134.242:8082/c2c",#U
                "username" => "luckysky",
                "password" => "Luckysky12345",
            ],
            "2" => [ #商铺ID
                "url" => "http://202.124.134.242:8082/c2c", #P
                "username" => "luckysky",
                "password" => "Luckysky12345",
            ],
            "3" => [ #商铺ID
                "url" => "http://202.124.134.242:8082/c2c",#Bataan
                "username" => "luckysky",
                "password" => "Luckysky12345",
            ],
        ],
        "online" => []
    ];

    private $_apiDomain = "";
    private $_username = "";
    private $_password = "";

    public function initConfig($env, $merchantId) {
        $this->_apiDomain = self::CONFIG[$env][$merchantId]['url'] ?? "";
        $this->_username = self::CONFIG[$env][$merchantId]['username'] ?? "";
        $this->_password = self::CONFIG[$env][$merchantId]['password'] ?? "";
        return $this;
    }

    public function send($extension, $telephone) {
        if (!$this->_apiDomain) {
            throw new Exception("SENBAC配置文件错误");
        }
        $data['extension'] = $extension;
        $data['number'] = $telephone;
        $data['username'] = $this->_username;
        $data['password'] = $this->_password;
        $res =  Curl::post($data, $this->_apiDomain);
        if($res){
            $res = json_decode($res, true);
            if(isset($res['success']) && $res['success']){
                return true;
            }
            throw new \Exception("The Call SENBAC ERROR:".json_encode($res));
        }
        throw new \Exception('The Call SENBAC interface failed');
    }

}
