<?php

namespace Common\Console\Services\Statistics\History;

use Common\Models\Statistics\StatisticsData;
use Common\Models\Statistics\StatisticsLog;
use Common\Services\BaseService;
use Common\Utils\Data\DateHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;

class StatisticsDataHistoryServer extends BaseService
{
    /**
     * @param string $startDate
     * @param string $endDate
     */
    public function history($startDate = '', $endDate = '')
    {
        if ($startDate == '') {
            $startDate = date('Y-m-d', strtotime('-1 day'));
        }
        if ($endDate == '') {
            $endDate = date('Y-m-d');
        }

        $nextDate = $startDate;
        while ($endDate != $nextDate) {
            echo $nextDate . PHP_EOL;
            $this->addStatistics($nextDate);
            $nextDate = date("Y-m-d", strtotime("{$nextDate} +1 days"));
        }
    }

    /**
     * @param $date
     * @return mixed
     */
    public function addStatistics($date)
    {
        StatisticsData::model()->deleteData([
            'date' => $date,
        ]);
        $merchantId = MerchantHelper::getMerchantId();
        $nowTime = DateHelper::dateTime();
        $statisticsData = StatisticsLog::model()->setConnection("mysql_readonly")->select(DB::raw("'{$merchantId}' as merchant_id, '{$date}' as date, statistics, count(*) as count, sum(quota) as quota, 
            client_id, quality, channel_id, platform, 1 as status, '{$nowTime}' as created_at,  '{$nowTime}' as updated_at"))
            ->where('created_date', $date)
            ->groupBy(['client_id', 'statistics', 'quality', 'channel_id', 'platform'])->get()->toArray();
        return StatisticsData::model()->addData($statisticsData);
    }


}
