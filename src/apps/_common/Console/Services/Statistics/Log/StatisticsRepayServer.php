<?php

namespace Common\Console\Services\Statistics\Log;

use Common\Models\Order\RepaymentPlan;
use Common\Models\Statistics\StatisticsLog;

class StatisticsRepayServer extends StatisticsBaseServer
{
    public $statistics = StatisticsLog::STATISTICS_REPAY;

    public function getDatas($startTime, $endTime)
    {
        return RepaymentPlan::model()
            ->select(['user_id', 'repay_time as time', 'repay_amount as quota'])
            ->whereBetween('repay_time', [$startTime, $endTime])
            ->get()->toArray();
    }
}