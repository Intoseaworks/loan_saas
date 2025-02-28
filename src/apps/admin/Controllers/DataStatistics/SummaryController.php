<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\DataStatistics;

use Admin\Controllers\BaseController;
use Admin\Services\DataStatistics\SummaryServer;

class SummaryController extends BaseController
{
    public function index()
    {
        $param = $this->request->all();
        $data = SummaryServer::server()->getList($param);
        return $this->resultSuccess($data);
    }

}
