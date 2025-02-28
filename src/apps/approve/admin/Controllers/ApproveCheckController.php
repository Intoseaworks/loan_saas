<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-01-08
 * Time: 16:18
 */

namespace Approve\Admin\Controllers;


use Approve\Admin\Controllers\Controller;
use Approve\Admin\Rules\ApproveCheckRule;
use Approve\Admin\Services\Check\ApproveCheckService;

class ApproveCheckController extends ApproveBaseController
{

    /**
     * ApproveCheckController constructor.
     * @param ApproveCheckRule $rule
     * @param ApproveCheckService $services
     */
    public function __construct(ApproveCheckRule $rule, ApproveCheckService $services)
    {
        parent::__construct();
        $this->rule = $rule;
        $this->server = $services;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function index()
    {
        try {

            if (!$this->rule->validate(ApproveCheckRule::SENARIO_INDEX, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }

            $data = $this->server->getList($this->params);
            return $this->resultSuccess($data);

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function setPriority()
    {
        try {

            if (!$this->rule->validate(ApproveCheckRule::SENARIO_INDEX, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }
            $id = $this->params['id'];
            $this->server->setPriority($id);
            return $this->resultSuccess();

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * @return array
     */
    public function orderStatusList()
    {
        return $this->resultSuccess($this->server->getOrderStatusList());
    }

    /**
     * @return array
     */
    public function approveStatusList()
    {
        try {

            if (!$this->rule->validate(ApproveCheckRule::SENARIO_APPROVE_STATUS, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }
            $orderStatus = $this->params['order_status'] ?? [];
            return $this->resultSuccess($this->server->approveStatusList($orderStatus));

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function show()
    {
        try {

            if (!$this->rule->validate(ApproveCheckRule::SENARIO_SHOW, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }
            $poolId = $this->params['id'];
            return $this->resultSuccess($this->server->detail($poolId));

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    public function backCase(){
        $poolIds = $this->getParam('pool_ids', []);
        if($poolIds){
            $poolIds = json_decode($poolIds, true);
            $res = $this->server->backCase($poolIds);
            return $this->resultSuccess("{$res} successes");
        }
        return $this->resultFail();
    }

    public function turnCase(){
        $poolIds = $this->getParam('pool_ids', []);
        $admin = $this->getParam('admin_user');
        if($poolIds){
            $poolIds = json_decode($poolIds, true);
            $res = $this->server->turnCase($poolIds,$admin);
            if ($res){
                return $this->resultSuccess(count($poolIds)." successes");
            }else{
                return $this->resultFail('审批中,挂起状态符合条件的单才可以转单!');
            }
        }
        return $this->resultFail();
    }

}
