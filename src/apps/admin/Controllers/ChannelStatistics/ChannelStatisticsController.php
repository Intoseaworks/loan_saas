<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\ChannelStatistics;

use Admin\Controllers\BaseController;
use Admin\Services\ChannelStatistics\ChannelStatisticsServer;

class ChannelStatisticsController extends BaseController
{
    public function index()
    {
        $param = $this->request->all();
        return $this->resultSuccess(ChannelStatisticsServer::server()->getList($param));
    }

    public function view()
    {
        $param = $this->request->all();
        return $this->resultSuccess(ChannelStatisticsServer::server()->getOne($param));
    }

}
