<?php

namespace Common\Jobs\Risk;

use Common\Jobs\Job;
use Common\Redis\Risk\RiskSendTaskDataRedis;
use Common\Services\Risk\RiskSendServer;
use Common\Services\Risk\RiskServer;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\MerchantHelper;
use JMD\Libs\Risk\RiskSend;

class RiskSendDataCommonJob extends Job
{
    public $queue = 'system-approve';

    public $tries = 3;

    protected $merchantId;
    protected $type;

    public function __construct($merchantId, $type = null)
    {
        $this->merchantId = $merchantId;
        $this->type = $type;

        if (is_null($type)) {
            $this->type = RiskSend::COMMON_ALL_TYPE;
        }
    }

    public function handle()
    {
        return;
        try {
            MerchantHelper::clearMerchantId();

            $res = RiskServer::server()->sendDataCommon($this->merchantId, $this->type);

            if (!$res->isSuccess()) {
                $this->retry($this->type);
                throw new \Exception($res->getMsg(), $res->getCode());
            }

            $data = $res->getData();
            $needRetryType = array_keys(array_filter($data, function ($item) {
                return $item != RiskServer::SEND_DATA_STATUS_SUCCESS;
            }));

            $this->retry($needRetryType);
        } catch (\Exception $e) {
            DingHelper::notice([
                'merchant_id' => $this->merchantId,
                'type' => $this->type,
                'msg' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
            ], '机审数据上传队列异常-common', DingHelper::AT_SOLIANG);
        }
    }

    protected function retry($type)
    {
        if (!$type) {
            return false;
        }

        $redisKey = 'common:' . $this->merchantId;
        $surplusTypes = RiskSendTaskDataRedis::redis()->add($redisKey, $type);

        if (!$surplusTypes) {
            return false;
        }
        // 失败次数未超限的类型进行重试
        return RiskSendServer::server()->sendCommonQueue($this->merchantId, $surplusTypes, 30);
    }
}
