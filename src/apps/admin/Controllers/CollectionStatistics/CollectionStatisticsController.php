<?php

namespace Admin\Controllers\CollectionStatistics;

use Admin\Controllers\BaseController;
use Admin\Services\CollectionStatistics\CollectionStatisticsServer;

class CollectionStatisticsController extends BaseController
{
    /**
     * 催收订单统计列表
     * @return array
     */
    public function list()
    {
        $param = $this->request->all();
        $data = CollectionStatisticsServer::server()->getList($param);
        return $this->resultSuccess($data);
    }

    /**
     * 催回率统计列表
     */
    public function rateList()
    {
        $param = $this->request->all();
        $data = CollectionStatisticsServer::server()->getRateList($param);
        return $this->resultSuccess($data);
    }

    /**
     * 催收员每日统计列表
     */
    public function staffList()
    {
        $param = $this->request->all();
        $data = CollectionStatisticsServer::server()->getStaffAchievementList($param);
        return $this->resultSuccess($data);
    }

    /**
     * 催收效率列表
     */
    public function efficiencyList()
    {
        $params = $this->getParams();
        $data = CollectionStatisticsServer::server()->getStaffEfficiencyList($params);
        return $this->resultSuccess($data);
    }
}
