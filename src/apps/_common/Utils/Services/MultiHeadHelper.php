<?php

namespace Common\Utils\Services;

use Common\Utils\DingDing\DingHelper;
use Common\Utils\Helper;
use JMD\JMD;
use JMD\Libs\Services\BaseRequest;

class MultiHeadHelper
{
    use Helper;

    const ROUTE_GET_MULTI_HEAD_DATA = '/api/multi-head/app/get-multi-head-data';

    const CHANNEL_KRAZYBEE = 'krazybee';
    const CHANNEL_KREDITBEE = 'kreditbee';
    const CHANNEL_CREDY = 'credy';
    const CHANNEL_PAY_SENSE = 'paySense';
    const CHANNEL_BADABRO = 'badaBro';
    const CHANNEL_TACHYLOANS = 'tachyLoans';
    const CHANNEL_SAHUKAR = 'sahukar';
    const CHANNEL_RUPEEMAX = 'rupeemax';
    const CHANNEL_CRAZYRUPEE = 'crazyrupee';
    const CHANNEL_ICERDIT = 'iCerdit';
    const CHANNEL_MPOKKET = 'mPokket';
    const CHANNEL_OCASH = 'ocash';
    const CHANNEL_PAYMEINDIA = 'paymeindia';
    const CHANNEL_LOANFLIX = 'loanflix';

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
            ], '【异常】印牛服务多头请求错误');
            return false;
        }
    }

    public function getMultiHeadData($mobile, $channels)
    {
        return $this->request(self::ROUTE_GET_MULTI_HEAD_DATA, [
            'mobile' => $mobile,
            'channels' => $channels,
        ], false);
    }
}
