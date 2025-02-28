<?php

namespace Common\Console\Services\Statistics\Log;

use Common\Models\Statistics\StatisticsLog;
use Common\Models\User\UserAuth;

class StatisticsAuthServer extends StatisticsBaseServer
{
    public $statistics = StatisticsLog::STATISTICS_AUTH;

    public function getDatas($startTime, $endTime)
    {
        return UserAuth::model()
            ->select(['user_id', 'time'])
            ->where('type', UserAuth::TYPE_COMPLETED)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->groupBy('user_id')
            ->get()->toArray();
    }
}