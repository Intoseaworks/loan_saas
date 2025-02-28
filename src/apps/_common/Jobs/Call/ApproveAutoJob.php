<?php

namespace Common\Jobs\Call;

use Admin\Services\Collection\CollectionCallServer;
use Common\Jobs\Job;
use Common\Models\Approve\ApproveCallLog;
use Common\Models\Approve\ApprovePool;
use Common\Models\Approve\ApproveUserPool;
use Common\Models\Call\CallAdmin;
use Common\Models\Call\CallLog;
use Common\Models\Order\Order;
use Common\Services\Order\OrderServer;
use Common\Utils\Code\OrderStatus;
use Common\Utils\MerchantHelper;

/**
 * Class ActionLogJob
 * @package App\Jobs
 * @author ChangHai Zhan
 */
class ApproveAutoJob extends Job {

    const EXT_LIST = [
        '8881' => 1,
        '8882' => 2,
        '8883' => 3,
        '8884' => 4,
        '8880' => 6,
        '8889' => 7,
        '8890' => 8,
    ];

    public $queue = 'call-collection';
    public $tries = 2;
    public $ext; #分机号
    public $eventData;

    public function __construct($ext, $eventData = "") {
        $this->ext = $ext;
        $this->eventData = $eventData;
    }

    public function handle() {
        $startNew = false;
        if (isset(self::EXT_LIST[$this->ext])) {
            MerchantHelper::setMerchantId(self::EXT_LIST[$this->ext]);
        } else {
            return;
        }
        $this->autoCallDated();
        if ($this->eventData) {
            $this->eventData = json_decode($this->eventData, true);
            $uuid = array_get($this->eventData, "Caller-Unique-ID");
            if ($uuid) {
                $isAnswer = 2;
                $log = CallLog::model()->where("uuid", $uuid)->first();
                
                $variableBillsec = array_get($this->eventData, "variable_billsec", 0);
                $variablePlaybackSeconds = array_get($this->eventData, "variable_playback_seconds", 0);
                $variableSipTermStatus = array_get($this->eventData, 'variable_sip_term_status', '');
                
                
                $callerName = array_get($this->eventData, "Caller-Caller-ID-Name");
                $callerNum = array_get($this->eventData, "Caller-Caller-ID-Number");
                
                if ($variableSipTermStatus == "200" && ($variableBillsec - $variablePlaybackSeconds) > 0 && $variableBillsec>21 && $callerName != $callerNum) {
                    $isAnswer = 1;
                }
                if ($log && $log->order_id) {
                    $startNew = true;
                    $updateApprove = ApprovePool::model()->where("order_id", $log->order_id)->orderByDesc("id")->first();
                    if ($updateApprove) {
                        if (in_array($updateApprove->auto_call_status, [ApprovePool::AUTO_CALL_STATUS_FIRST_CALLING, ApprovePool::AUTO_CALL_STATUS_FIRST_WAIT])) {
                            if ($isAnswer == 1) {
                                $updateApprove->auto_call_status = ApprovePool::AUTO_CALL_STATUS_FIRST_PASS;
                                $updateApprove->call_test_status = 1;
                            } else {
                                $updateApprove->auto_call_status = ApprovePool::AUTO_CALL_STATUS_TWICE_WAIT;
                                $updateApprove->call_test_status = 2;
                                $this->addApproveCallLog($updateApprove);
                            }
                        }
                        if (in_array($updateApprove->auto_call_status, [ApprovePool::AUTO_CALL_STATUS_TWICE_CALLING, ApprovePool::AUTO_CALL_STATUS_FIRST_WAIT])) {
                            if ($isAnswer == 1) {
                                $updateApprove->auto_call_status = ApprovePool::AUTO_CALL_STATUS_TWICE_PASS;
                                $updateApprove->call_test_status = 1;
                            } else {
                                $updateApprove->auto_call_status = ApprovePool::AUTO_CALL_STATUS_TWICE_FAIL;
                                $updateApprove->call_test_status = 2;
                            }
                        }
                        $updateApprove->auto_call_time = date("Y-m-d H:i:59");
                        if (in_array($updateApprove->auto_call_status, [ApprovePool::AUTO_CALL_STATUS_TWICE_FAIL, ApprovePool::AUTO_CALL_STATUS_FIRST_PASS, ApprovePool::AUTO_CALL_STATUS_TWICE_PASS])) {
                            $this->returnPool($updateApprove);
                        }
                        $updateApprove->save();
                        echo "Saved" . PHP_EOL;
                    }
                }
            }
        }
        /** 时间判断自动外呼时间
          每天：8:00-21:00 */
        if (!$this->checkTime()) {
            return;
        }
        echo "MerchantID:" . self::EXT_LIST[$this->ext];
        if ($startNew || !$this->eventData) {
            echo "new call " . PHP_EOL;
            $approve = ApprovePool::model()->where(function($query) {
                        $query->where("auto_call_status", ApprovePool::AUTO_CALL_STATUS_FIRST_WAIT)
                                ->orWhere(function($query1) {
                                    $query1->where("auto_call_status", ApprovePool::AUTO_CALL_STATUS_TWICE_WAIT)
                                    ->where("auto_call_time", "<", date("Y-m-d H:i:s", time() - 60 * 20));
                                });
                    })->orderBy("auto_call_time")->first();
            if ($approve) {
                echo " Order:{$approve->order_id} telephone:{$approve->order->user->telephone} " . PHP_EOL;
                $res = CollectionCallServer::server()->call(["telephone" => $approve->order->user->telephone, "order_id" => $approve->order_id, "type" => CallAdmin::TYPE_APPROVE_AUTO],
                        $this->ext, '2001' . self::EXT_LIST[$this->ext], 1);
                if ($res->isSuccess()) {
                    echo "Successful" . PHP_EOL;
                    if ($approve->auto_call_status == ApprovePool::AUTO_CALL_STATUS_FIRST_WAIT) {
                        $approve->auto_call_status = ApprovePool::AUTO_CALL_STATUS_FIRST_CALLING;
                    }
                    if ($approve->auto_call_status == ApprovePool::AUTO_CALL_STATUS_TWICE_WAIT) {
                        $approve->auto_call_status = ApprovePool::AUTO_CALL_STATUS_TWICE_CALLING;
                    }
                    $approve->auto_call_time = date("Y-m-d H:i:s");
                    $approve->save();
                } else {
                    echo "Unsuccessful" . PHP_EOL;
                }
            }else{
                sleep(3);
                dispatch(new ApproveAutoJob($this->ext));
                echo "无案件" . PHP_EOL;
            }
        }
    }

    public function returnPool(ApprovePool $updateApprove) {
        $this->addApproveCallLog($updateApprove);
        $updateApprove->status = ApprovePool::STATUS_WAITING;
        $updateApprove->order_status = OrderStatus::CALL_APPROVAL;
        $updateApprove->type = ApprovePool::ORDER_CALL_GROUP;
        $userPool = ApproveUserPool::model()->where("approve_pool_id", $updateApprove->id)->orderByDesc("id")->first();
        if ($userPool) {
            $userPool->status = ApproveUserPool::STATUS_STOP_WORK;
            $userPool->save();
        }
        $updateApprove->save();
        try{
            OrderServer::server()->changeStatus($updateApprove->order_id, [Order::STATUS_WAIT_TWICE_CALL_APPROVE], Order::STATUS_WAIT_CALL_APPROVE);
        }catch(\Exception $e){
            echo $e->getMessage();
        }
    }
    
    public function addApproveCallLog(ApprovePool $updateApprove){
        $approveCallLog = [
            "approve_pool_id" => $updateApprove->id,
            "approve_user_pool_id" => "",
            "admin_id" => "0",
            "order_id" => $updateApprove->order_id,
            "telephone" => $updateApprove->order->user->telephone,
            "name" => $updateApprove->order->user->fullname,
            "tv1" => "Phone No. of IVR",
            "tv2" => $updateApprove->call_test_status ==1 ? "answer" : "noanswer"
        ];
        $userPool = ApproveUserPool::model()->where("approve_pool_id", $updateApprove->id)->orderByDesc("id")->first();
        if ($userPool) {
            $approveCallLog['approve_user_pool_id'] = $userPool->id;
        }
        ApproveCallLog::model()->createModel($approveCallLog);
    }

    # 处理超长呼叫中的订单进入下一步

    public function autoCallDated() {
        $list = ApprovePool::model()->whereIn("auto_call_status", [ApprovePool::AUTO_CALL_STATUS_FIRST_CALLING, ApprovePool::AUTO_CALL_STATUS_TWICE_CALLING])->where("auto_call_time", "<", date("Y-m-d H:i:s", time() - 60 * 20))->orderBy("auto_call_status")->get();
        foreach ($list as $apprive) {
            switch ($apprive->auto_call_status) {
                case ApprovePool::AUTO_CALL_STATUS_FIRST_CALLING:
                    $apprive->auto_call_status = ApprovePool::AUTO_CALL_STATUS_TWICE_WAIT;
                    $apprive->auto_call_time = date("Y-m-d H:i:s");
                    $apprive->save();
                    break;
                case ApprovePool::AUTO_CALL_STATUS_TWICE_CALLING:
                    $apprive->auto_call_status = ApprovePool::AUTO_CALL_STATUS_TWICE_FAIL;
                    $this->returnPool($apprive);
                    $apprive->auto_call_time = date("Y-m-d H:i:00");
                    $apprive->save();
                    break;
            }
        }
    }

    private function checkTime() {
        $hour = ["08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21"];
        $currentHour = date("H");
        return in_array($currentHour, $hour);
    }

}
