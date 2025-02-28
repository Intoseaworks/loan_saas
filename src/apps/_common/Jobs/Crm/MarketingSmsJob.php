<?php

namespace Common\Jobs\Crm;

use Common\Jobs\Job;
use Common\Models\Crm\CrmMarketingTask;
use Common\Models\Crm\Customer;
use Common\Models\Crm\MarketingSmsLog;
use Common\Models\Crm\SmsTemplate;
use Common\Models\Crm\MarketingPhoneAssign;
use Common\Utils\Sms\SmsEgpHelper;
use Yunhan\Utils\Env;
use Admin\Models\User\UserBlack;
use Admin\Services\Crm\WhiteListServer;
use Common\Redis\CommonRedis;
use Common\Utils\MerchantHelper;
use Common\Models\Merchant\App;

class MarketingSmsJob extends Job {

    public $queue = 'marketing-sms';
    public $tries = 1;
    public $taskId;

    public function __construct($taskId) {
        $this->taskId = $taskId;
    }

    public function handle() {
        if(!$this->taskId){
            echo "任务ID未设置";
            return;
        }
        echo "开始发送TaskId:".$this->taskId.PHP_EOL;
        $task = CrmMarketingTask::model()->getOne($this->taskId);
        if (!$task) {
            echo "任务未找到";
            return;
        }
        MerchantHelper::helper()->setMerchantId($task->merchant_id);
        $smsTemplate = SmsTemplate::model()->getOne($task->sms_template_id);
        if (!$smsTemplate) {
            echo "短信模板不存在";
            return;
        }
        if ($task->task_type == CrmMarketingTask::TYPE_SMS) {
            /*$query = Customer::model()->newQuery();
            $query->where("type", $task->customer_type);
            $batchIds = json_decode($task->batch_id, true);
            if (is_array($batchIds)) {
                $query->whereIn("batch_id", $batchIds);
            }
            $customerStatus = json_decode($task->customer_status, true);
            if (is_array($customerStatus)) {
                $query->whereIn("status", $customerStatus);
            }*/
//            echo $query->toSql();
            # 本次发送只执行一次
            if($task->sms_run_times>=1 && $task->frequency == '1'){
                echo "只本次执行";
                return;
            }
            $list = \Admin\Services\Crm\MarketingServer::server()->getTaskCustomer($task);
//            print_r($list);
            foreach ($list as $customer) {
                $sended = false;
                
                $task->send_total += 1;
                # 停止限定-判断是否转电销
                $stopTerm = json_decode($task->phone_stop_term, true);
                if (is_array($stopTerm) && in_array(CrmMarketingTask::STOP_TERM_STATUS_CHANGE, $stopTerm) && $customer->getStatusStopDays() == 1) {
                    $lastStatus = $this->getCustomerStatus($customer->id, $task->id);
                    if ($lastStatus && $lastStatus != $customer->status) {
                        echo $customer->telephone . "状态变更停止发送[$lastStatus<>$customer->status]" . PHP_EOL;
                        continue;
                    }
                }
                if (is_array($stopTerm) && in_array(CrmMarketingTask::STOP_TERM_ENTER_TELEMARKETING, $stopTerm) && $this->isTelemarketing($customer->id)) {
                    echo $customer->telephone . "已进入电销任务" . PHP_EOL;
                    continue;
                }
                # 灰名单
                if (UserBlack::model()->isActive()->whereTelephone($customer->telephone)->exists()) {
                    echo $customer->telephone . "触发灰名单" . PHP_EOL;
                    continue;
                }
                # 黑名单
                $chkData = [
                    "telephone" => $customer->telephone,
                    "email" => $customer->email,
                    "id_number" => $customer->id_number,
                ];
                if (WhiteListServer::server()->checkBlackList($chkData)->count() > 0) {
                    echo $customer->telephone . "触发黑名单" . PHP_EOL;
                    continue;
                }
                # 发送频率
                if ($task->frequency == "2") {
                    $frequencyDetail = json_decode($task->frequency_detail, true);
                    if (is_array($frequencyDetail)) {
                        $stopDays = $customer->getStatusStopDays();
                        if (!in_array($stopDays, $frequencyDetail)) {
                            echo $customer->telephone . "{$task->frequency}不在按状态停留时间{$stopDays}" . PHP_EOL;
                            continue;
                        }
                    }
                }
                if ($task->frequency == "3") {
                    $frequencyDetail = json_decode($task->frequency_detail, true);
                    $days = round((time() - strtotime($task->created_at)) / 86400);
                    if (is_array($frequencyDetail) && !in_array($days, $frequencyDetail)) {
                        echo $customer->telephone . "{$task->frequency}不在按状态停留时间{$days}" . PHP_EOL;
                        continue;
                    }
                }

                # 当日是否发送过
                $rdsKey = md5("{$this->taskId}-{$customer->id}-{$customer->telephone}-{$smsTemplate->id}" . date("Y-m-d"));
                $exists = CommonRedis::redis()->verifyCount($rdsKey, false);
                if ($exists == 0) {
                    if (Env::isProd()) {
                        $appId = App::model()->where("merchant_id", $task->merchant_id)->get()->first()->id;
                        $sendId = App::getDataById($appId, 'send_id');
                        $sended = SmsEgpHelper::helper()->sendMarketing($customer->telephone, $smsTemplate->tpl_content, [], $sendId);
                    } else {
                        $sended = true;
                    }
                    if ($sended) {
                        CommonRedis::redis()->verifyCount($rdsKey);
                    }
                    echo 'sended' . PHP_EOL;
                    MarketingSmsLog::model()->createModel([
                        "merchant_id" => $task->merchant_id,
                        "task_id" => $this->taskId,
                        "customer_id" => $customer->id,
                        "sms_template_id" => $smsTemplate->id,
                        "telephone" => $customer->telephone,
                        "content" => $smsTemplate->tpl_content,
                        "status" => $sended ? 1 : 0,
                        "customer_status" => $customer->status
                    ]);
                    $task->success_total += 1;
                } else {
                    echo "今日已发送" . $customer->telephone . PHP_EOL;
                }
            }
        }

        $task->sms_run_times += 1;
        $task->save();
        echo $this->taskId." end";
    }

    /*
     * 是否进入电销
     */

    public function isTelemarketing($customerId) {
        $query = MarketingPhoneAssign::model()->newQuery();
        $query->where("customer_id", $customerId);
        $query->where("status", "1");
        $query->where("merchant_id", MerchantHelper::helper()->getMerchantId());
        return $query->count();
    }

    public function getCustomerStatus($customerId, $taskId) {
        $query = MarketingSmsLog::model()->newQuery();
        $query->where("customer_id", $customerId);
        $query->where("task_id", $taskId);
        $query->where("merchant_id", MerchantHelper::helper()->getMerchantId());
        $query->orderByDesc("id");
        $res = $query->first();
        return isset($res->customer_status) ? $res->customer_status : "";
    }

}
