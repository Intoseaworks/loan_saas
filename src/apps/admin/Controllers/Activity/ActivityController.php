<?php

/**
 * @author Roc
 */

namespace Admin\Controllers\Activity;

use Admin\Rules\Activity\ActivityRule;
use Admin\Services\Activity\ActivityServer;
use Common\Response\ApiBaseController;

class ActivityController extends ApiBaseController {

    /**
     * 优惠券列表
     * @return type
     */
    public function index() {
        $params = $this->getParams();
        $res = ActivityServer::server()->list($params);
        return $this->resultSuccess($res);
    }

    /**
     * 新建优惠券任务下拉列表
     * @return type
     */
    public function all() {
        $res = ActivityServer::server()->listAll();
        return $this->resultSuccess($res);
    }



    /**
     * 优惠券信息查看
     */
    public function view() {
        $res = ActivityServer::server()->view($this->getParam('id'));
        if ($res) {
            return $this->resultSuccess($res);
        }
        return $this->resultFail();
    }

    /**
     * 添加优惠券信息
     */
    public function addActivity(ActivityRule $rule) {
        $params = $this->getParams();
        if (!$rule->validate(ActivityRule::SCENARIO_ACTIVITY_CREATE, $params)) {
            return $this->resultFail($rule->getError());
        }
        $res = ActivityServer::server()->addActivity($params);
        if ($res->isSuccess()) {
            return $this->resultSuccess();
        }
        return $this->resultFail($res->getMsg());
    }

    public function setActivity(){
        $res = ActivityServer::server()->setActivity($this->getParams());
        if ($res->isSuccess()) {
            return $this->resultSuccess();
        }
        return $this->resultFail($res->getMsg());
    }

}
