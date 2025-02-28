<?php

namespace Common\Console\Services\Statistics\Log;

use Common\Console\Services\Statistics\StatisticsLogServer;
use Common\Services\BaseService;

class StatisticsBaseServer extends BaseService
{
    public $statistics;

    public function add($startTime, $endTime)
    {
        $datas = $this->getDatas($startTime, $endTime);
        if (!$datas) {
            return $this->outputError('该时间段无数据');
        }
        StatisticsLogServer::server()->delLogs($this->statistics, $startTime, $endTime);
        $statisticsLogServer = StatisticsLogServer::server();
        $statisticsLogServer->addlogs($this->statistics, $datas);
        if ($statisticsLogServer->isError()) {
            echo $statisticsLogServer->getMsg() . PHP_EOL;
        }
    }

    public function getDatas($startTime, $endTime)
    {
    }
}