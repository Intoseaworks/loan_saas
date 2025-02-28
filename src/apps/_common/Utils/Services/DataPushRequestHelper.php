<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/24
 * Time: 10:45
 */

namespace Common\Utils\Services;


use Common\Utils\Email\EmailHelper;
use Common\Utils\Helper;
use JMD\JMD;
use JMD\Libs\Services\SaasService;

class DataPushRequestHelper
{
    use Helper;

    const ROUTE_DATA_PUSH_CARD = '/saas/data/push';

    public $params = [];
    public $route;

    /**
     * @param $route
     * @param $params
     * @return bool|\JMD\Libs\Services\DataFormat
     */
    public function push($method, $data)
    {
        try {
            JMD::init(['projectType' => 'lumen']);
            $res = SaasService::request(self::ROUTE_DATA_PUSH_CARD, [
                'method' => $method,
                'data' => $data
            ]);
            return $res;
        } catch (\Exception $e) {
            EmailHelper::send([
                'method' => $method,
                'e' => $e->getMessage(),
                'data' => $data
            ], '【异常】Saas data推送印牛服务');
            return false;
        }
    }

}
