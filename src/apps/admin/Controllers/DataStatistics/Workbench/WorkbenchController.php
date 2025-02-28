<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\DataStatistics\Workbench;

use Admin\Controllers\BaseController;
use Admin\Services\DataStatistics\WorkbenchLineServer;
use Admin\Services\DataStatistics\WorkbenchServer;
use Common\Utils\Data\DateHelper;

class WorkbenchController extends BaseController {

    public function index() {
        $param = $this->request->all();
        $data = WorkbenchServer::server()->getIndex($param);
        return $this->resultSuccess($data);
    }

    public function line() {
        $param = $this->request->all();
        $data = WorkbenchLineServer::server()->getLine($param);
        return $this->resultSuccess($data);
    }

    public function search() {
        $param = $this->request->all();
        $startDate = $param['date'][0];
        $endDate = $param['date'][1];
        $serverWorkbench = WorkbenchServer::server();
        $serverWorkbench->todayTimeStart = $startDate;
        $serverWorkbench->todayTimeNow = $endDate . ' 23:59:59';
        $serverWorkbench->todayTimeEnd = $endDate . ' 23:59:59';
        $days = DateHelper::diffInDays($startDate, $endDate)+1;
        $serverWorkbench->yesterdayTimeStart = DateHelper::subDays($days, 'Y-m-d', $startDate);
        $serverWorkbench->yesterdayTimeNow = DateHelper::subDays(1, 'Y-m-d', $startDate) . ' 23:59:59';
        $serverWorkbench->yesterdayTimeEnd = DateHelper::subDays(1, 'Y-m-d', $startDate) . ' 23:59:59';
        //print_r($serverWorkbench);
        $data = $serverWorkbench->getIndex([]);
        return $this->resultSuccess($data);
    }

}
