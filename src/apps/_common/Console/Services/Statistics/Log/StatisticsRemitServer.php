<?php

namespace Common\Console\Services\Statistics\Log;

use Common\Models\Order\Order;
use Common\Models\Statistics\StatisticsLog;

class StatisticsRemitServer extends StatisticsBaseServer
{
    public $statistics = StatisticsLog::STATISTICS_REMIT;

    public function getDatas($startTime, $endTime)
    {
        return Order::model()
            ->select(['user_id', 'paid_time as time', 'paid_amount as quota'])
            ->whereBetween('paid_time', [$startTime, $endTime])
            ->get()->toArray();
    }
}