<?php

namespace Common\Console\Services\Statistics\History;

use Common\Console\Services\Statistics\Log\StatisticsAuthServer;
use Common\Console\Services\Statistics\Log\StatisticsLoanServer;
use Common\Console\Services\Statistics\Log\StatisticsOverdueServer;
use Common\Console\Services\Statistics\Log\StatisticsPassServer;
use Common\Console\Services\Statistics\Log\StatisticsRegisterServer;
use Common\Console\Services\Statistics\Log\StatisticsRemitServer;
use Common\Console\Services\Statistics\Log\StatisticsRepayServer;
use Common\Services\BaseService;
use Common\Utils\Data\DateHelper;

class StatisticsLogHistoryServer extends BaseService
{
    public function history($startTime = '', $endTime = '')
    {
        if ($startTime == '') {
            //前一天时间戳
            $startTime = DateHelper::parseDate('yesterday');
            $endTime = DateHelper::date();
        }

        //try {
        //注册
        StatisticsRegisterServer::server()->add($startTime, $endTime);
        //完成认证
        StatisticsAuthServer::server()->add($startTime, $endTime);
        //借款申请
        StatisticsLoanServer::server()->add($startTime, $endTime);
        //审核通过
        StatisticsPassServer::server()->add($startTime, $endTime);
        //放款
        StatisticsRemitServer::server()->add($startTime, $endTime);
        //还款
        StatisticsRepayServer::server()->add($startTime, $endTime);
        //逾期
        StatisticsOverdueServer::server()->add($startTime, $endTime);

        /*//身份认证
        StatisticsIdentityServer::server()->add($startTime, $endTime);
        //基础认证
        //StatisticsAuthBaseServer::server()->add($startTime, $endTime);
        //高级认证
        //StatisticsAuthHighServer::server()->add($startTime, $endTime);
        //创建订单
        StatisticsAddOrderServer::server()->add($startTime, $endTime);
        //机审通过
        StatisticsSystemServer::server()->add($startTime, $endTime);
        //人审通过
        StatisticsManualServer::server()->add($startTime, $endTime);
        //确认借款
        StatisticsSignOrderServer::server()->add($startTime, $endTime);
        //放款人数，金额
        StatisticsLoanServer::server()->add($startTime, $endTime);*/
        /*} catch (\Exception $exception) {
            var_dump($exception->getMessage());exit();
        }*/
    }


}