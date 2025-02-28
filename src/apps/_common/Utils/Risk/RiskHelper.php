<?php

namespace Common\Utils\Risk;

use common\models\User\User;
use JMD\JMD;
use JMD\Libs\Risk\BaseRequest;
use JMD\Libs\Risk\DataFormat;
use JMD\Libs\Risk\RiskSend;

class RiskHelper
{
    /**
     * 返回发送数据类
     * @param User $user
     * @param null $taskNo
     * @return RiskSend
     */
    public static function getRiskSendObj(User $user, $taskNo = null): RiskSend
    {
        [$domain, $appKey, $secretKey] = self::getConfig();

        JMD::init(['projectType' => 'lumen']);
        $request = new RiskSend($appKey, $secretKey, $domain);
        $request->setUserId($user->id);
        if (!empty($taskNo)) {
            $request->setTaskNo($taskNo);
        }

        return $request;
    }

    public static function getRiskSendCommonObj(): RiskSend
    {
        [$domain, $appKey, $secretKey] = self::getConfig();

        JMD::init(['projectType' => 'lumen']);
        $request = new RiskSend($appKey, $secretKey, $domain);
        $request->isSendCommon();
        return $request;
    }

    /**
     * 请求 Risk 接口
     * @param $router
     * @param $data
     * @return DataFormat
     */
    public static function sendRiskRequest($router, $data = null)
    {
        [$domain, $appKey, $secretKey] = self::getConfig();

        JMD::init(['projectType' => 'lumen']);
        $request = new BaseRequest($appKey, $secretKey);
        $request->setDomain($domain);
        $request->setUrl($router);
        $request->setData($data);

        return new DataFormat($request->execute());
    }

    public static function getConfig()
    {
        $config = config('domain.public_services_config');

        return [
            str_finish(array_get($config, 'risk_endpoint'), '/'),
            array_get($config, 'app_key'),
            array_get($config, 'app_secret_key'),
        ];
    }
}
