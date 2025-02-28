<?php

namespace Common\Console\Services\Statistics\Log;

use Common\Models\Order\Order;
use Common\Models\Statistics\StatisticsLog;

class StatisticsPassServer extends StatisticsBaseServer
{
    public $statistics = StatisticsLog::STATISTICS_PASS;

    public function getDatas($startTime, $endTime)
    {
        return Order::model()
            ->select(['user_id', 'pass_time as time'])
            ->whereBetween('pass_time', [$startTime, $endTime])
            ->get()->toArray();
    }
}