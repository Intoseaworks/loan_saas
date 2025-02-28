<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/21
 * Time: 11:10
 */

namespace Common\Services\Bankcard;

use Common\Services\BaseService;
use Common\Services\Pay\BasePayServer;
use Common\Utils\Email\EmailHelper;
use Illuminate\Support\Facades\Cache;
use JMD\JMD;
use JMD\Libs\Services\PayService;

class BankcardServer extends BaseService
{
    /**
     * 获取银行卡bin（废弃）
     * @param $bankcardNo
     * @return mixed
     */
    public function bin($bankcardNo)
    {
        try {
            $cacheKey = 'bin:' . $bankcardNo;
            /** 设置缓存 24小时 */
            return Cache::remember($cacheKey, 24 * 60, function () use ($bankcardNo) {
                $autoPayPlatform = BasePayServer::server()->getAutoPayChannel();
                /** 获取商户放款配置支付渠道 */
                $tradePlatform = current($autoPayPlatform);
                JMD::init(['projectType' => 'lumen']);
                $payServer = new PayService();
                $api = $payServer->bankcardBin;
                $payServer->request->setUrl($api);
                $payServer->request->setData([
                    'bankcard_no' => $bankcardNo,
                    'trade_platform' => $tradePlatform
                ]);
                $result = $payServer->request->execute();
                if ($result->isSuccess()) {
                    return $result->getData();
                }
                EmailHelper::send($result->getAll(), '卡bin查询失败');
            });
        } catch (\Exception $e) {
            EmailHelper::sendException($e, '卡bin查询失败');
            return false;
        }
    }
}
