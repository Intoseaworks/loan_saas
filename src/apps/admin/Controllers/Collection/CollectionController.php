<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\Collection;

use Admin\Controllers\BaseController;
use Admin\Rules\Collection\CollectionRule;
use Admin\Rules\Order\OrderRule;
use Admin\Services\Collection\CollectionServer;
use Common\Models\Collection\CollectionAdmin;
use Illuminate\Http\Request;
use Admin\Services\Collection\CollectionDeductionServer;
use Admin\Services\Collection\CollectionOnlineServer;
use Common\Services\Push\EmailServer;

class CollectionController extends BaseController {

    public function myOrderIndex(CollectionRule $rule) {
        $param = $this->request->all();
        if (!$rule->validate(CollectionRule::SCENARIO_LIST, $param)) {
            return $this->resultFail($rule->getError());
        }
        $data = CollectionServer::server()->getList($param, true);
        return $this->resultSuccess($data);
    }

    public function myOrderView(CollectionRule $rule) {
        if (!$rule->validate(CollectionRule::SCENARIO_DETAIL, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $res = CollectionServer::server()->getOne($this->request->input('id'), true);
        return $this->resultSuccess(CollectionServer::server()->getCollectionData($res));
    }

    public function orderIndex(CollectionRule $rule) {
        $param = $this->request->all();
        if (!$rule->validate(CollectionRule::SCENARIO_LIST, $param)) {
            return $this->resultFail($rule->getError());
        }
        $data = CollectionServer::server()->getList($param);
        return $this->resultSuccess($data);
    }

    public function orderView(CollectionRule $rule) {
        if (!$rule->validate(CollectionRule::SCENARIO_DETAIL, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $res = CollectionServer::server()->getOne($this->request->input('id'));
        return $this->resultSuccess(CollectionServer::server()->getCollectionData($res));
    }

    public function setOrderPartRepayOn(CollectionRule $rule) {
        $params = $this->request->all();
        if (!$rule->validate(CollectionRule::SCENARIO_SET_ORDER_PART_REPAY_ON, $params)) {
            return $this->resultFail($rule->getError());
        }
        return $this->resultSuccess(CollectionServer::server()->setOrderPartRepayOn($params));
    }

    /**
     * 催收排除名单列表
     * @param OrderRule $rule
     * @return array
     */
    public function collectionBlackList(OrderRule $rule) {
        $param = $this->request->all();
        if (!$rule->validate(CollectionRule::SCENARIO_LIST, $param)) {
            return $this->resultFail($rule->getError());
        }
        $data = CollectionServer::server()->getList($param);
        return $this->resultSuccess($data);
    }

    public function switchBlackList() {
        CollectionServer::server()->switchBlackList($this->getParam('user_id'));
        return $this->resultSuccess();
    }

    /**
     * 展期试算
     * @param CollectionRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     */
    public function renewalCalc(CollectionRule $rule) {
        $params = $this->request->all();
        if (!$rule->validate(CollectionRule::SCENARIO_LIST, $params)) {
            return $this->resultFail($rule->getError());
        }
        $data = CollectionServer::server()->renewalCalc($params);
        return $this->resultSuccess($data);
    }

    /**
     * 线下导入导入外部黑名单
     * @param CollectionRule $rule
     * @param Request $request
     * @return array
     */
    public function importBlackList(CollectionRule $rule, Request $request) {
        if (!$rule->validate($rule::SCENARIO_IMPORT_BLACKLIST, $this->getParams())) {
            return $this->resultFail($rule->getError());
        }

        $server = CollectionServer::server()->importBlacklist($request);
        return $server->isSuccess() ? $this->resultSuccess(null, $server->getMsg()) : $this->resultFail($server->getMsg());
    }

    /**
     * 减免申请
     */
    public function deductionApply() {
        $params = $this->getParams();
//        $params['admin_id'] = \Common\Utils\LoginHelper::getAdminId();
        $res = CollectionDeductionServer::server()->apply($params);
        if ($res) {
            return $this->resultSuccess();
        }
        return $this->resultFail();
    }

    /**
     * 等待审核的减免
     */
    public function deductionWaitApprove() {
        $params = $this->getParams();
        $params['status'] = ['1'];
        $res = CollectionDeductionServer::server()->applyList($params);
        if ($res) {
            return $this->resultSuccess($res);
        }
    }

    /**
     * 减免历史
     */
    public function deductionHistory() {
        $params = $this->getParams();
        $params['status'] = ['2', '3'];
        $res = CollectionDeductionServer::server()->applyList($params);
        if ($res) {
            return $this->resultSuccess($res);
        }
    }

    /**
     * 审批减免
     */
    public function deductionApprove() {
        $params = $this->getParams();
        $res = CollectionDeductionServer::server()->approve($params);
        if ($res) {
            return $this->resultSuccess($res);
        }
        return $this->resultSuccess();
    }

    public function online() {
        return $this->resultSuccess(CollectionOnlineServer::server()->online());
    }

    public function offline() {
        return $this->resultSuccess(CollectionOnlineServer::server()->offline($this->getParams()));
    }

    public function status() {
        return $this->resultSuccess(CollectionOnlineServer::server()->status());
    }

    public function warning() {
        return $this->resultSuccess(CollectionOnlineServer::server()->warning());
    }

    public function report() {
        return $this->resultSuccess(CollectionOnlineServer::server()->reportNew($this->getParams()));
    }

    public function sendmail() {
        $res = EmailServer::server()->collectionSend($this->getParam('collection_id'));
        if ($res->isSuccess()) {
            return $this->resultSuccess();
        }
        return $this->resultFail($res->getMsg());
    }

    public function todayFinish(){
        return $this->resultSuccess(CollectionOnlineServer::server()->todayFinish());
    }
}
