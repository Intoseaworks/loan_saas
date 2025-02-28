<?php

namespace Common\Console\Services\Statistics\Log;

use Common\Models\Order\Order;
use Common\Models\Statistics\StatisticsLog;

class StatisticsLoanServer extends StatisticsBaseServer
{
    public $statistics = StatisticsLog::STATISTICS_LOAN;

    public function getDatas($startTime, $endTime)
    {
        return Order::model()
            ->select(['user_id', 'created_at as time', 'principal as quota'])
            ->whereBetween('created_at', [$startTime, $endTime])
            ->get()->toArray();
    }
}