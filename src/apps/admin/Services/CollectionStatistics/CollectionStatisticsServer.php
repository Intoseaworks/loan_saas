<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\CollectionStatistics;

use Admin\Exports\CollectionStatistics\CollectionStatisticsExport;
use Admin\Models\CollectionStatistics\StatisticsCollection;
use Admin\Models\CollectionStatistics\StatisticsCollectionRate;
use Admin\Models\CollectionStatistics\StatisticsCollectionStaff;
use Admin\Services\BaseService;
use Common\Models\CollectionStatistics\StatisticsCollectionEfficiency;
use Common\Models\CollectionStatistics\StatisticsCollectionStaffAchievement;
use Common\Redis\CollectionStatistics\CollectionStatisticsRedis;
use Common\Utils\Data\StatisticsHelper;
use Common\Utils\MerchantHelper;
use Yunhan\Utils\Env;

class CollectionStatisticsServer extends BaseService
{
    /**
     * 催收订单统计列表
     * @param $param
     * @return mixed
     */
    public function getList($param)
    {
        $size = array_get($param, 'size', 15);
        $query = StatisticsCollection::model()->search($param);
        if ($this->getExport()) {
            $queryData = $query->get();
            $data = $queryData;
        } else {
            $queryData = $query->paginate($size);
            $data = $queryData;
        }

        foreach ($this->getExport() ? $data : $data->items() as $key => $item) {
            $item->collection_success_rate_text = StatisticsHelper::percent($item->today_overdue_finish, $item->today_overdue) . '%';//今日催回率
            $item->collection_success_count_rate_text = StatisticsHelper::percent($item->overdue_finish_count, $item->overdue_count) . '%';//累计催回率
        }

        if ($this->getExport()) {
            try {
                CollectionStatisticsExport::getInstance()->export($data, CollectionStatisticsExport::SCENE_ORDER_LIST, false);
            } catch (\Exception $e) {
                var_dump($e);
                exit();
            }
        }
        return $data;
    }

    /**
     * 催回率统计列表
     * @param $param
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getRateList($param)
    {
        $size = array_get($param, 'size', 15);
        $query = StatisticsCollectionRate::model()->search($param);

        if ($this->getExport()) {
            $queryData = $query->get();
            $data = $queryData;
        } else {
            $queryData = $query->paginate($size);
            $data = $queryData;
        }

        foreach ($this->getExport() ? $data : $data->items() as $key => $item) {
            $item->today_collection_rate_text = StatisticsHelper::percent($item->today_overdue_finish, $item->present_overdue_count + $item->today_overdue_finish) . '%';//当日回款率
            $item->history_collection_rate_text = StatisticsHelper::percent($item->present_overdue_finish_count, $item->history_overdue_count) . '%';//截止当前回款率
        }

        if ($this->getExport()) {
            try {
                CollectionStatisticsExport::getInstance()->export($data, CollectionStatisticsExport::SCENE_RATE_LIST, false);
            } catch (\Exception $e) {
                var_dump($e);
                exit();
            }
        }
        return $data;
    }

    /**
     * 催收员每日统计列表
     * @param $param
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getStaffList($param)
    {
        $size = array_get($param, 'size');
        $query = StatisticsCollectionStaff::model()->search($param);
        if ($this->getExport()) {
            CollectionStatisticsExport::getInstance()->export($query, CollectionStatisticsExport::SCENE_WORKER_LIST);
        }

        $list = $query->paginate($size);
        //@phan-suppress-next-line PhanTypeNoPropertiesForeach
        foreach ($list as $data) {
            $data->getText();
        }

        return $list;
    }

    /**
     * 催收效率
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection|void
     * @throws \Common\Exceptions\ApiException
     */
    public function getStaffEfficiencyList($params)
    {
        $size = array_get($params, 'size', 15);
        if (!($merchantId = MerchantHelper::getMerchantId())) {
            return $this->outputException('merchant error');
        }
        $query = StatisticsCollectionEfficiency::model()->search($params);

        if ($this->getExport()) {
            $queryData = $query->get();
            $data = $queryData;
        } else {
            $queryData = $query->paginate($size);
            $data = $queryData;
        }
        $dateFormat = "%Y-%m-%d";
        if(isset($params['date_type'])){
            switch ($params['date_type']){
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
        foreach ($this->getExport() ? $data : $data->items() as $key => $item) {
            $item->finish_rate = ($item->assign_cnt ? StatisticsHelper::numberFormat($item->finish_cnt / $item->assign_cnt * 100) : 0) . '%';
            $item->collection_rate = ($item->assign_cnt ? StatisticsHelper::numberFormat($item->collection_cnt / $item->assign_cnt * 100) : 0) . '%';
            $item->total_amount = 0;//StatisticsCollection::model()->collectTotalAmount($item->assign_data, $item->level, $dateFormat, $item->admin_id);
            $item->finish_amount_rate = 0;//$item->total_amount ? StatisticsHelper::numberFormat($item->finish_amount/$item->total_amount * 100) . '%' : 0;
        }

        if ($this->getExport()) {
            try {
                CollectionStatisticsExport::getInstance()->export($data, CollectionStatisticsExport::SCENE_EFFICIENCY_LIST, false);
            } catch (\Exception $e) {
                var_dump($e);
                exit();
            }
        }

        return $data;
    }

    public function getStaffAchievementList($params)
    {
        $size = array_get($params, 'size', 15);
        if (!($merchantId = MerchantHelper::getMerchantId())) {
            return $this->outputException('merchant error');
        }
        $query = StatisticsCollectionStaffAchievement::model()->searchNew($params);

        if ($this->getExport()) {
            $queryData = $query->get();
            $data = $queryData;
        } else {
            $queryData = $query->paginate($size);
            $data = $queryData;
        }

        foreach ($this->getExport() ? $data : $data->items() as $key => $item) {
            $item->today_assign_finish_rate = StatisticsHelper::numberFormat($item->today_assign_finish_rate * 100) . '%';
            $item->finish_rate = StatisticsHelper::numberFormat($item->finish_rate * 100) . '%';
            $item->bad_rate = StatisticsHelper::numberFormat($item->bad_rate * 100) . '%';
        }

        if ($this->getExport()) {
            try {
                CollectionStatisticsExport::getInstance()->export($data, CollectionStatisticsExport::SCENE_WORKER_LIST, false);
            } catch (\Exception $e) {
                var_dump($e);
                exit();
            }
        }

        return $data;
    }

    public function resetCollectionStatistics()
    {
        if (Env::isProd()) {
            return false;
        }
        StatisticsCollection::query()->delete();
        StatisticsCollectionRate::query()->delete();
        StatisticsCollectionStaff::query()->delete();

        CollectionStatisticsRedis::redis()->resetStatistics();

        return true;
    }
}
