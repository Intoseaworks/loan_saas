<?php

namespace Common\Console\Services\Statistics;

use Common\Models\Statistics\StatisticsLog;
use Common\Models\User\User;
use Common\Services\BaseService;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\DateHelper;
use Common\Utils\MerchantHelper;

class StatisticsLogServer extends BaseService
{
    public function delLogs($statistics, $startTime, $endTime)
    {
        StatisticsLog::model()->deleteLog($statistics, $startTime, $endTime);
    }

    public function addlogs($statistics, $datas)
    {
        $statisticsLogInsert = [];
        $this->getUserFrom($datas);
        if (!$datas) {
            return $this->outputError('没有用户数据');
        }
        foreach ($datas as $data) {
            $registerTime = array_get($data, 'register_time');
            $dateTime = array_get($data, 'time');
            $statisticsLog = [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'user_id' => array_get($data, 'id'),
                'statistics' => $statistics,
                'quota' => array_get($data, 'quota', 1),
                'client_id' => array_get($data, 'client_id'),
                'quality' => array_get($data, 'quality'),
                'channel_id' => array_get($data, 'channel_id'),
                'platform' => array_get($data, 'platform'),
                'status' => StatisticsLog::STATUS_NORMAL,
                'created_at' => DateHelper::dateTime(),
                'updated_at' => DateHelper::dateTime(),

                'created_time' => $dateTime,
                'created_date' => DateHelper::formatToDate($dateTime),
                'created_hour' => DateHelper::format($dateTime, 'H'),
                'created_day' => DateHelper::format($dateTime, 'd'),
                'created_week' => strftime('%U', strtotime($dateTime)),
                'created_month' => DateHelper::format($dateTime, 'm'),
                'created_year' => DateHelper::format($dateTime, 'Y'),

                'user_register_time' => $registerTime,
                'user_register_date' => DateHelper::formatToDate($registerTime),
                'user_register_hour' => DateHelper::format($registerTime, 'H'),
                'user_register_day' => DateHelper::format($registerTime, 'd'),
                'user_register_week' => strftime('%U', strtotime($registerTime)),
                'user_register_month' => DateHelper::format($registerTime, 'm'),
                'user_register_year' => DateHelper::format($registerTime, 'Y'),

            ];
            $statisticsLogInsert[] = $statisticsLog;
        }
        if (count($statisticsLogInsert) == 0) {
            return $this->outputError('没有统计数据');
        }
        StatisticsLog::model()->addLog($statisticsLogInsert);
    }

    public function getUserFrom(&$datas)
    {
        $userIds = array_column($datas, 'user_id');
        $users = User::select(['id', 'created_at as register_time', 'client_id', 'quality', 'channel_id', 'platform', 'quality_time'])
            ->whereIn('id', $userIds)
            ->get()->toArray();
        $usersKeyById = ArrayHelper::arrayChangeKey($users, 'id');
        foreach ($datas as $key => &$data) {
            $time = array_get($data, 'time');
            if (!($userId = array_get($data, 'user_id'))) {
                unset($datas[$key]);
                continue;
            }
            if (!($userData = array_get($usersKeyById, $userId))) {
                unset($datas[$key]);
                continue;
            }
            if (DateHelper::formatToDate($time) == DateHelper::formatToDate(array_get($userData, 'register_time'))) {
                $userData['quality'] = User::QUALITY_REGISTER;
            }
            $data = array_merge($data, $userData);
        }
    }


}
