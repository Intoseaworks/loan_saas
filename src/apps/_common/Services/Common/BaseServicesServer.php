<?php

namespace Common\Services\Common;

use Common\Services\BaseService;
use JMD\App\Utils;
use JMD\JMD;
use JMD\Libs\Services\BaseRequest;

class BaseServicesServer extends BaseService
{
    /** 应用注册路由地址 */
    public $routeRegisterApp = 'third/external/app/register';

    /** 短信json数据拉取 */
    public $routeSmsJsonList = 'api/send/sms-json-list';


    public function registerApp($appName)
    {
        $params = [
            'app_name' => $appName,
        ];
        return $this->execute($this->routeRegisterApp, $params);
    }

    protected function execute($url, $params)
    {
        JMD::init(['projectType' => 'lumen']);
        $config = Utils::getParam('public_services_config');
        $request = new BaseRequest();
        $request->setAppKey($config['app_key']);
        $request->setSecretKey($config['app_secret_key']);
        $request->setUrl($url);
        $request->setData($params);
        return $request->execute();
    }

    /**
     * 获取短信列表
     * @param $params
     * @return \JMD\Libs\Services\DataFormat
     */
    public function getSmsList($params)
    {
        return $this->execute($this->routeSmsJsonList, $params);
    }
}
