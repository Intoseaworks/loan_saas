<?php

namespace Tests\UnitTest\Risk;

use Common\Events\Risk\RiskDataSendEvent;
use Common\Models\SystemApprove\SystemApproveTask;
use Common\Services\Risk\RiskSendServer;
use Common\Services\Risk\RiskServer;
use Common\Utils\MerchantHelper;
use JMD\Libs\Risk\RiskSend;
use Tests\Admin\TestBase;

class RiskDataSendTest extends TestBase
{
    public function testSendNodeEvent()
    {
        $res = event(new RiskDataSendEvent(null, RiskDataSendEvent::NODE_USER_REGISTER, 923));
        dd($res);
    }

    public function testSendByType()
    {
        $userId = 210;

        $res = RiskSendServer::server()->register($userId);

        dd($res);
    }

    public function testSendDataAll()
    {
        $type = [
            RiskSend::TYPE_ORDER,
        ];

//        $res = RiskSendServer::server()->sendQueue(210, $type);
//        dd($res);

        $userId = 210;
        $res = RiskServer::server()->sendDataAll($userId, $type);

//        $data = $res->getData();
//        $needRetryType = array_keys(array_filter($data, function ($item) {
//            return $item == RiskServer::SEND_DATA_STATUS_SUCCESS;
//        }));

        dd($res);
    }

    public function testSendDataCommon()
    {
        MerchantHelper::setMerchantId(1);

        $type = [
            RiskSend::COMMON_TYPE_CHANNEL,
        ];

        $res = RiskSendServer::server()->sendCommonQueue(1, $type);
        dd($res);

        $res = RiskServer::server()->sendDataCommon(37);

        dd(MerchantHelper::getMerchantId(), $res);
    }

    public function testT()
    {
        $key = [
            "BANK_CARD",
            "COLLECTION_RECORD",
            "ORDER",
            "ORDER_DETAIL",
            "REPAYMENT_PLAN",
            "USER",
            "USER_AUTH",
            "USER_CONTACT",
            "USER_INFO",
            "USER_THIRD_DATA",
            "USER_WORK",
        ];

        $userId = 192;
        $task = SystemApproveTask::find(897);

        $res = RiskSendServer::server()->sendQueue($userId, $key, $task);

//        $res = RiskSendTaskDataRedis::redis()->add('192_897', $key);

        dd($res);
    }
}
