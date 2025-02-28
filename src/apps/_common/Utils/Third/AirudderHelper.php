<?php

namespace Common\Utils\Third;

use Common\Utils\Helper;
use Common\Utils\MerchantHelper;
use Risk\Common\Models\Third\ThirdDataAirudder;
use Yunhan\Utils\Env;
use Api\Models\User\UserContact;

class AirudderHelper {

    use Helper;
    const TIMEOUT = "11";

    private $_appKey = '4553429da1cc8aedf330f3eab5f6a8c7';
    private $_appSecret = 'db01e28b2730e1c1475f32ba8ebb77bb';
    private $_url = "https://detection.airudder.com/service/cloud/emptycheck";
    private $_callback = "https://saas.e-perash.com/app/callback/airudder";
//    private $_callback = "http://120.78.230.66/app/callback/airudder";
    private $_areaNum = "+63";

    /**
     * 号码状态0=未知;1无效号码;2有效号码;3正忙;4号码无法使用（欠费）5呼叫保持;6关机;7不在服务区;8号码未注册激活;9号码无效或错误号码
     */
    const STATUS_ALIAS = [
        0 => '未知',
        1 => '无效号码',
        2 => '有效号码',
        3 => '正忙',
        4 => '号码无法使用(欠费)',
        5 => '呼叫保持',
        6 => '关机',
        7 => '不在服务区',
        8 => '号码未注册激活',
        9 => '号码无效或错误号码',
        10 => '号码未做检测',
        11 => '超时'
    ];

    public function query($telephone) {
        # 暂停 检测
        $sql = "UPDATE user_contact SET check_status='10' WHERE (check_status='' OR check_status is null) AND contact_telephone = '{$telephone}';";
        \DB::update($sql);
        return true;
        # 暂定代码结束
        if (!Env::isProd()) {
            return false;
        }
        $id = uniqid();
        $mobile = substr($telephone, -10);
        if (!in_array(substr($mobile, 0, 1), [8, 9]) || !is_numeric($mobile)) {
            UserContact::where("contact_telephone", "LIKE", "%{$mobile}")->update(['check_status' => 10]);
            return true;
        }
        
        if($airdder = ThirdDataAirudder::model()->where('telephone', $this->_areaNum . $mobile)->where("status",'<>', '0')->first())
        {
            echo $telephone."-".$airdder->status.PHP_EOL;
            echo $sql = "UPDATE user_contact SET check_status='{$airdder->status}' WHERE (check_status='' OR check_status is null) AND contact_telephone = '{$telephone}';";
            \DB::update($sql);
            return true;
        }
        if(ThirdDataAirudder::model()->where('telephone', $this->_areaNum . $mobile)->where("status", '0')->where("created_at", "<", date("Y-m-d H:i:s", time()-600))->exists())
        {
            \DB::update("UPDATE user_contact SET check_status='".self::TIMEOUT."' WHERE (check_status='' OR check_status is null) AND contact_telephone = '{$telephone}';");
            return self::TIMEOUT;
        }
        $mobile = $this->_areaNum . $mobile;
        if (ThirdDataAirudder::model()->where('telephone', $mobile)->where("created_at", ">", date("Y-m-d"))->exists()) {
            return true;
        }
        $postData = [
            "calls" => [
                [
                    "callee" => $mobile,
                    "callback" => base64_encode($this->_callback),
                    "prvdata" => $id
                ],
            ],
        ];
        $res = $this->post($postData);
        $attributes = [
            "telephone" => $mobile,
            "init_request" => json_encode($postData),
            "init_response" => json_encode($res),
            "merchant_id" => MerchantHelper::getMerchantId(),
            "order_id" => $id,
            "created_at" => date("Y-m-d H:i:s")
        ];
        ThirdDataAirudder::model()->createModel($attributes);
        return $res;
    }

    public function urlsafe_b64encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/'), array('-', '_'), $data);
        return $data;
    }

    public function post($postData) {

        //$postData = ["8613681111385"];
        $timestemp = time();
        $version = '1.0.0';
        $strSign = $this->_appSecret . '+' . $timestemp . '+' . $version;
        $param = [
            "system" => [
                'appkey' => $this->_appKey,
                'charset' => "UTF-8",
                'timestamp' => $timestemp,
                'signtype' => "MD5",
                "sign" => $this->urlsafe_b64encode(pack('H*', md5($strSign))),
                "version" => $version
            ],
            "business" => $postData
        ];
        $ch = curl_init();
        $url = $this->_url;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
        $res = curl_exec($ch);
        //curl_error($ch);
        return json_decode($res, true);
    }

}
