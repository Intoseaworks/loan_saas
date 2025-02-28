<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/1
 * Time: 10:10
 */

namespace Admin\Services\ChannelStatistics;

use Admin\Exports\ChannelStatistics\ChannelStatisticsExport;
use Admin\Models\Channel\Channel;
use Admin\Models\User\User;
use Admin\Services\BaseService;
use Common\Redis\CommonRedis;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\StatisticsHelper;
use Common\Utils\MerchantHelper;

class ChannelStatisticsServer extends BaseService
{
    /**
     * @param $param
     * @return mixed
     */
    public function getList($param)
    {
        $dateFormat = "%Y-%m-%d";
        if(isset($param['date_type'])){
            switch ($param['date_type']){
                case "Y":
                    $dateFormat = "%Y";
                    break;
                case "M":
                    $dateFormat = "%Y/%m";
                    break;
                case "U":
                    $dateFormat = "%Y#%U";
                    break;
            }
        }
        $param['dateFormat'] = $dateFormat;
        $query = User::model()->searchStatistics($dateFormat);
        if ($channelName = array_get($param, 'channel_code', '')) {
            $query->where('channel.channel_code', $channelName);
        }

        if ($appVersion = array_get($param, 'app_version', '')) {
            $query->whereIn('user.app_version', (array)$appVersion);
        }

        //注册时间
        $registerTimeStart = array_get($param, 'register_time_start', '');
        $registerTimeEnd = array_get($param, 'register_time_end', '');
        if ($registerTimeStart && $registerTimeEnd) {
            $query->whereBetween('user.created_at',
                [$registerTimeStart, $registerTimeEnd]);
        }

        $merchantId = MerchantHelper::getMerchantId();
        $pageSize = array_get($param, 'page', 1) . '-' . array_get($param, 'size', 15);
        $appVersion = ArrayHelper::arrayToJson($appVersion);
        $channelKey = "channelStatistics:{$merchantId}-{$appVersion}-{$channelName}-{$registerTimeStart}-{$registerTimeEnd}-{$pageSize}-{$dateFormat}";
        if (!$this->getExport() && ($redisData = CommonRedis::redis()->get($channelKey))) {
            return json_decode($redisData, true);
        }

//        if ($time = array_get($param, 'time')) {
//            if (count($time) == 2) {
//                $start = current($time);
//                $end = last($time);
//                $query->whereBetween('user.created_at', [$start, $end]);
//            }
//        }

        $query->groupBy('user.app_version', \DB::raw('DATE_FORMAT(user.created_at,"'.$dateFormat.'")'), 'channel.channel_code');
        $query->orderBy('date', 'desc');
        $query->orderBy('user.app_version', 'desc');
        $query->orderBy('channel.channel_code', 'desc');

        // 导出
        /*if ($this->getExport()) {
            ChannelStatisticsExport::getInstance()->export($query, ChannelStatisticsExport::SCENE_CHANNEL_NAME_LIST);
        }*/

        if ($this->getExport()) {
            $queryData = $query->get();
            $data = $queryData;
        } else {
            $queryData = $query->paginate();
            $data = $queryData;
        }
        $authQuery = $this->statisticsAuth($param);
        if ($this->getExport()) {
            $authQueryData = $authQuery->get();
            $authData = $authQueryData;
        } else {
            $authQueryData = $authQuery->paginate();
            $authData = $authQueryData->items();
        }
        foreach ($this->getExport() ? $data : $data->items() as $key => $item) {
            $item->setScenario(User::SCENARIO_STATISTICS)->getText();
            $item->base_info_count = $authData[$key]->base_info_count;
            $item->base_info_rate = StatisticsHelper::percent($item->base_info_count, $item->user_count);
            $item->contacts_count = $authData[$key]->contacts_count;
            $item->contacts_rate = StatisticsHelper::percent($item->contacts_count, $item->user_count);
            $item->aadhaar_card_kyc_count = $authData[$key]->aadhaar_card_kyc_count;
            $item->aadhaar_card_kyc_rate = StatisticsHelper::percent($item->aadhaar_card_kyc_count, $item->user_count);
            $item->aadhaar_card_count = $authData[$key]->aadhaar_card_count;
            $item->aadhaar_card_rate = StatisticsHelper::percent($item->aadhaar_card_count, $item->user_count);
            $item->faces_count = $authData[$key]->faces_count;
            $item->faces_rate = StatisticsHelper::percent($item->faces_count, $item->user_count);
            $item->pan_card_count = $authData[$key]->pan_card_count;
            $item->pan_card_rate = StatisticsHelper::percent($item->pan_card_count, $item->user_count);
            $item->address_voter_count = $authData[$key]->address_voter_count;
            $item->address_voter_rate = StatisticsHelper::percent($item->address_voter_count, $item->user_count);
            $item->address_passport_count = $authData[$key]->address_passport_count;
            $item->address_passport_rate = StatisticsHelper::percent($item->address_passport_count, $item->user_count);
            $item->user_extra_info_count = $authData[$key]->user_extra_info_count;
            $item->user_extra_info_rate = StatisticsHelper::percent($item->user_extra_info_count, $item->user_count);
            $item->bankcard_count = $authData[$key]->bankcard_count;
            $item->bankcard_rate = StatisticsHelper::percent($item->bankcard_count, $item->user_count);
            if (!$item->channel_code) {
                $item->channel_code = t('自然流量', 'statistics');
            }
        }

        if ($this->getExport()) {
            ChannelStatisticsExport::getInstance()->export($data, ChannelStatisticsExport::SCENE_CHANNEL_NAME_LIST, false);
        }

        CommonRedis::redis()->set($channelKey, json_encode($data, 256), 60);

        return $data;
    }

    /**
     * @param $param
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function statisticsAuth($param)
    {
        $dateFormat = isset($param['dateFormat']) ? $param['dateFormat'] : "%Y-%m-%d";
        $query = User::model()->searchStatisticsAuth($dateFormat);
        if ($channelName = array_get($param, 'channel_code')) {
            $query->where('channel.channel_code', $channelName);
        }

        if ($appVersion = array_get($param, 'app_version')) {
            $query->whereIn('user.app_version', (array)$appVersion);
        }

        //注册时间
        $register_time_start = array_get($param, 'register_time_start');
        $register_time_end = array_get($param, 'register_time_end');
        if ($register_time_start && $register_time_end) {
            $query->whereBetween('user.created_at',
                [$register_time_start, $register_time_end]);
        }

        if ($time = array_get($param, 'time')) {
            if (count($time) == 2) {
                $start = current($time);
                $end = last($time);
                $query->whereBetween('user.created_at', [$start, $end]);
            }
        }
        $query->groupBy('user.app_version', \DB::raw('DATE_FORMAT(user.created_at,"'.$dateFormat.'")'), 'channel.channel_code');
        $query->orderBy('date', 'desc');
        $query->orderBy('user.app_version', 'desc');
        $query->orderBy('channel.channel_code', 'desc');

        return $query;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getOne($param)
    {
        $query = Channel::model()->searchStatistics();
        if ($channelName = array_get($param, 'channel_name')) {
            $query->where('channel.channel_name', '=', $channelName);
        }
        if ($channelCode = array_get($param, 'channel_code')) {
            $query->where('channel.channel_code', 'like', '%' . $channelCode . '%');
        }
        $query->groupBy('channel.channel_code');

        // 导出
        if ($this->getExport()) {
            ChannelStatisticsExport::getInstance()->export($query, ChannelStatisticsExport::SCENE_CHANNEL_CODE_LIST);
        }

        $data = $query->paginate();

        foreach ($data->items() as $item) {
            $item->setScenario(Channel::SCENARIO_STATISTICS)->getText();
        }
        return $data;
    }
}
