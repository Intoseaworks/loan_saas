<?php

namespace Common\Response;

use JMD\App\lumen\Utils;
use JMD\Libs\Services\BaseRequest;
use JMD\Utils\SignHelper;
use Common\Models\Cloud\ApiUser;
use Common\Utils\Host\HostHelper;

class ServicesApiBaseController extends ApiBaseController
{
    
    protected $_ip = '1.1.1.1';
    /**
     * 验签
     */
    protected function validateSign()
    {
        $params = $this->request->except($this->request->path());

        $config = Utils::getParam(BaseRequest::CONFIG_NAME);
        $checkSign = SignHelper::validateSign($params, $config['app_secret_key']);

        return $checkSign;
    }
    
    protected function sign($params) {
        if (isset($params['secret']) && $params['secret']) {
            $secret = $params['secret'];
            unset($params['secret']);
        } else {
            return $this->resultFail("Missing parameter secret");
        }
        $accessName = $params['access_name'];
        $user = ApiUser::model()->where("username", $accessName)->first();
        if ($user && $user->status == ApiUser::STATUS_NORMAL) {
            # 1 数组排序
            ksort($params);
            # 2 拼装字符串
            $strProcessing = [];
            foreach ($params as $key => $val) {
                $strProcessing[] = $key . "#" . $val;
            }
            $strProcessing = implode('|', $strProcessing) . "|" . $user->access_key;
            $md5Str = md5($strProcessing);
            $this->_ip = HostHelper::getIp();
            # IP访问控制
            if ($user->access_ip) {
                if (stripos($this->_ip, $user->access_ip) === false) {
                    return $this->resultFail("IP: {$this->_ip} Access forbidden");
                }
            }
            if ($secret != $md5Str) {
                return $this->resultFail("Request validation failed" . ($accessName == 'theo' ? "($strProcessing)[md5:{$md5Str}]" : ""));
            } else {
                return $user->id;
            }
        } else {
            return $this->resultFail("User does not exist or is invalid");
        }
    }
}
