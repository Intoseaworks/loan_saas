<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\DataStatistics;

use Admin\Models\User\User;
use Admin\Services\BaseService;

class SummaryServer extends BaseService
{

    public function getList($param)
    {
        $query = User::model()->dailySummary($param);
        if ($appVersion = array_get($param, 'app_version')) {
            $query->where('user.app_version', '=', $appVersion);
        }
        $query->groupBy(['user.client_id', 'user.app_version']);

        // 导出
//        if ($this->getExport()) {
//            ChannelStatisticsExport::getInstance()->export($query, ChannelStatisticsExport::SCENE_CHANNEL_NAME_LIST);
//        }

        $data = $query->paginate();
        /*foreach ($data->items() as $item) {
            $item->setScenario(Channel::SCENARIO_STATISTICS)->getText();
        }*/
        return $data;
    }

}
