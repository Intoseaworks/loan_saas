<?php

namespace Common\Console\Services\Statistics\Log;

use Common\Models\Statistics\StatisticsLog;
use Common\Models\User\User;

class StatisticsRegisterServer extends StatisticsBaseServer
{
    public $statistics = StatisticsLog::STATISTICS_REGISTER;

    public function getDatas($startTime, $endTime)
    {
        return User::model()
            ->select(['id AS user_id', 'created_at AS time'])
            ->whereBetween('created_at', [$startTime, $endTime])
            ->get()->toArray();
    }
}