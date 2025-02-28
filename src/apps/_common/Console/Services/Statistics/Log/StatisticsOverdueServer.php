<?php

namespace Common\Console\Services\Statistics\Log;

use Common\Models\Order\Order;
use Common\Models\Statistics\StatisticsLog;
use Illuminate\Support\Facades\DB;

class StatisticsOverdueServer extends StatisticsBaseServer
{
    public $statistics = StatisticsLog::STATISTICS_OVERDUE;

    public function getDatas($startTime, $endTime)
    {
        return Order::model()
            ->select(DB::raw('user_id, overdue_time as time, sum(principal+principal*daily_rate) as quota'))
            ->whereBetween('overdue_time', [$startTime, $endTime])
            ->get()->toArray();
    }
}