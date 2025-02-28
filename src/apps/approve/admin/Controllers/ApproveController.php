<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-13
 * Time: 20:20
 */

namespace Approve\Admin\Controllers;

use Approve\Admin\Rules\ApproveRule;
use Approve\Admin\Services\Approval\ApproveService;
use Approve\Admin\Services\CommonService;
use Carbon\Carbon;
use Common\Models\Config\Config;
use Common\Utils\LoginHelper;
use Illuminate\Support\Facades\Artisan;
use Approve\Admin\Services\Approval\CallApproveService;
use Common\Models\Approve\ApproveCallLog;
use Common\Models\Approve\ApproveUserPool;

class ApproveController extends ApproveBaseController
{
    /**
     * ApproveController constructor.
     * @param ApproveRule $rule
     * @param ApproveService $services
     */
    public function __construct(ApproveRule $rule, ApproveService $services)
    {
        parent::__construct();
        $this->rule = $rule;
        $this->server = $services;
    }

    /**
     * 审批列表
     * @return array
     * @throws \Exception
     */
    public function index()
    {
        try {

            if (!$this->rule->validate(ApproveRule::SENARIO_INDEX, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }

            $data = $this->server->getUnFinishOrder($this->userId, $this->params);
            return $this->resultSuccess($data);

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * 开始审批
     * @return array
     * @throws \Exception
     */
    public function startWork()
    {
        try {
            Artisan::call("approve:push-to-pool");

            if (!LoginHelper::getAdminId()) {
                return $this->resultFail('登陆过期！请重新登陆');
            }

            if (!$this->rule->validate(ApproveRule::SENARIO_START_WORK, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }


            $data = $this->server->startWork($this->userId, $this->params);
            //判断人工审批最大审批单数指每个审批账号名下审批中的订单数最大的数量。包括初审，电审，挂起
            $manualMaxCount = (int)Config::getApproveManualMaxCount();
            $adminNowApproveCount = $this->server->getApproveOrderCount($this->userId);
            if ($manualMaxCount < $adminNowApproveCount) {
                return $this->resultFail('your approve orders '.$adminNowApproveCount.' is up to limit '.$manualMaxCount.'!Please refresh the web page again!');
            }
            return $this->resultSuccess($data);

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * 停止审批
     * @return array
     * @throws \Exception
     */
    public function stopWork()
    {
        try {

            if (!$this->rule->validate(ApproveRule::SENARIO_STOP_WORK, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }
            # 若审批人名下存在超过24个小时的未处理完成的订单（包括待初审，待电审，待电二审），则不能领新件
            $admin24hourOrder = $this->server->get24HOrder($this->userId);
            if($admin24hourOrder){
                return $this->resultFail('There are cases in your queue that have not been processed for 24 hours, please prioritize them for completion.'.$admin24hourOrder->order_no);
            }

            $type = array_get($this->params, 'type', '');
            if ($this->server->stopWork($this->userId, $type)) {
                return $this->resultSuccess([]);
            } else {
                return $this->resultFail('system error');
            }

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * 初审详情
     * @return array|mixed
     * @throws \Exception
     */
    public function firstShow()
    {
        try {
            if (!$this->rule->validate(ApproveRule::SENARIO_FIRST_APPROVE_VIEW, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }

            return $this->resultSuccess($this->server->firstApproveView($this->params));

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * 电审详情
     * @return array
     * @throws \Exception
     */
    public function callShow()
    {
        try {
            if (!$this->rule->validate(ApproveRule::SENARIO_FIRST_APPROVE_VIEW, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }

            return $this->resultSuccess($this->server->callApproveView($this->params));

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function callSubmit()
    {
        try {
            if(isset($this->params['emergency_contact_other'])){
                foreach($this->params['emergency_contact_other'] as $key => $value){
                    if(isset($value['name'])){
                        unset($this->params['emergency_contact_other'][$key]);
                    }
                }
            }
            if (!$this->rule->validate(ApproveRule::SENARIO_CALL_SUBMIT, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }

            $data = $this->server->callApproveSubmit($this->params);
            return $this->resultSuccess(['order_id' => $data['order_id'], 'id' => $data['id']]);

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }
    
    public function onlySave() {
        $data = $this->server->onlySave($this->params);
        return $this->resultSuccess();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function callSubmitDraft()
    {
        $cacheKey = 'CallSubmitDraft:' . \Common\Utils\MerchantHelper::getMerchantId().$this->params['id'];
        \Cache::forever($cacheKey, $this->params);
        return $this->params;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function firstSubmit()
    {
//        dd(555);
        try {
            if (!$this->rule->validate(ApproveRule::SENARIO_FIRST_SUBMIT, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }
            if (!empty($this->params['birthday'])) {
                $date = $this->params['birthday'];
                $carbon = Carbon::parse ($date); // 格式化一个时间日期字符串为 carbon 对象
                $int = $carbon->diffInYears((new Carbon), false); // $int 为正负数
                if ($int < 15 || $int > 70){
                    return $this->resultFail('your age lower than 15 or older than 70!');
                }
                $this->params['birthday'] = strtotime($date);
            }

            $data = $this->server->firstApproveSubmit($this->params);
            if ($data == 'Card number update failed, failed reason: already exist'){
                $this->server->cardNumberExist($this->params['order_id']);
                return $this->resultFail($data);
            }
            $this->server->cardNumberClear($this->params['order_id']);
            return $this->resultSuccess(['order_id' => $data['order_id'], 'id' => $data['id']]);

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function approveLog()
    {
        try {
            if (!$this->rule->validate(ApproveRule::SENARIO_APPROVE_LOG, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }

            return $this->resultSuccess($this->server->getApproveLog($this->params));

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function startCheck()
    {
        try {
            if (!$this->rule->validate(ApproveRule::SENARIO_START_CHECK, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }

            $this->server->startCheck($this->params['id']);
            return $this->resultSuccess();

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function addressInfo()
    {
        try {
            if (!$this->rule->validate(ApproveRule::SENARIO_ADDRESS_INFO, $this->params)) {
                return $this->sendError($this->rule->getError());
            }

            $pincode = $this->params['pincode'];
            return $this->resultSuccess(CommonService::getInstance()->getAddress($pincode));

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    public function addCallLog() {
        $params = $this->getParams();
        $userPool = ApproveUserPool::where('id', array_get($params, "approve_user_pool_id"))->first();
        $res = CallApproveService::getInstance($userPool->order_id)->addCallLog($userPool, $params);
        if ($res) {
            return $this->resultSuccess();
        }
        return $this->resultFail();
    }

    public function callLogList() {
        $res = CallApproveService::getInstance($this->getParam('order_id'))->callLogList($this->getParams());
        return $this->resultSuccess($res);
    }

    public function callLogSelect() {
        return $this->resultSuccess(["tv1" => ApproveCallLog::TV1, "tv2" => ApproveCallLog::TV2]);
    }

    public function backCase(){
        $poolIds = $this->getParam('pool_ids', []);
        if ($poolIds) {
            $poolIds = json_decode($poolIds, true);
            return $this->resultSuccess($this->server->backCase($poolIds));
        }
        return $this->resultFail('system error');
    }
    
    public function options() {
        $res = [
            "order_type" => \Common\Models\Approve\ApprovePool::ORDER_TYPE
        ];
        
        return $this->resultSuccess($res);
    }
}
