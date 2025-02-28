<?php

/**
 * @author Roc
 */

namespace Admin\Controllers\Activity;

use Admin\Services\Activity\ActivitiesRecordServer;
use Common\Response\ApiBaseController;

class ActivityStatisticsController extends ApiBaseController {

    /**
     * 活动统计列表
     * @return type
     */
    public function index() {
        $params = $this->getParams();
        $res = ActivitiesRecordServer::server()->list($params);
        return $this->resultSuccess($res);
    }

    /**
     * 活动统计列表导出
     * @return type
     */
    public function export() {
        $params = $this->getParams();
        $res = ActivitiesRecordServer::server()->export($params);
        return $this->resultSuccess($res);
    }

    /**
     * 活动统计列表导出
     * @return type
     */
    public function exportInvite() {
        $params = $this->getParams();
        $res = ActivitiesRecordServer::server()->exportInvite($params);
        return $this->resultSuccess($res);
    }

    /**
     * 中奖记录信息查看
     */
    public function view() {
        $res = ActivitiesRecordServer::server()->view($this->getParam('id'));
        if ($res) {
            return $this->resultSuccess($res);
        }
        return $this->resultFail();
    }

    /**
     * 添加中奖记录信息
     */
    public function addCouponReceive() {
        $res = ActivitiesRecordServer::server()->addCouponReceive($this->getParams());
        if ($res->isSuccess()) {
            return $this->resultSuccess();
        }
        return $this->resultFail();
    }

    public function setCouponReceive(){
//        $res = ActivitiesRecordServer::server()->setCouponReceive($this->getParams());
//        if ($res->isSuccess()) {
//            return $this->resultSuccess();
//        }
//        return $this->resultFail();
    }

}
