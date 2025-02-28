<?php

namespace Risk\Common\Helper\Services;

use Common\Utils\DingDing\DingHelper;
use JMD\JMD;
use JMD\Libs\Services\BaseRequest;

class ServicesHelper
{
    const ROUTE_AUTH_HM_CREDIT_REPORT = '/auth/third/credit-report/high-mark-generate'; // HM征信报告获取
    const ROUTE_AUTH_EXPERIAN_CREDIT_REPORT = '/auth/third/credit-report/experian-generate'; // Experian征信报告获取

    public function hmCreditReport($params)
    {
        return $this->request(self::ROUTE_AUTH_HM_CREDIT_REPORT, $params);
    }

    public function request($route, $params, $isPost = true)
    {
        try {
            JMD::init(['projectType' => 'lumen']);
            $request = new BaseRequest();
            $request->setUrl($route);
            $request->setData($params);

            return $request->execute($isPost);
        } catch (\Exception $e) {
            DingHelper::notice([
                'route' => $route,
                'postData' => $params,
                'e' => $e->getMessage(),
                'file' => $e->getFile() . ":" . $e->getLine(),
            ], '【异常】风控请求微服务错误');
            return false;
        }
    }

    public function experianCreditReport($params)
    {
        return $this->request(self::ROUTE_AUTH_EXPERIAN_CREDIT_REPORT, $params);
    }
}
