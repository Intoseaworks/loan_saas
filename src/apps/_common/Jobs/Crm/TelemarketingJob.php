<?php

namespace Common\Jobs\Crm;

use Common\Jobs\Job;
use Common\Models\Crm\CrmMarketingTask;
use Common\Models\Crm\Customer;
use Common\Models\Crm\MarketingPhoneAssign;
use Common\Models\Crm\MarketingPhoneJob;
use Common\Models\Crm\MarketingPhoneLog;
use Common\Utils\Data\DateHelper;
use Common\Utils\MerchantHelper;
use Common\Models\Crm\CustomerStatus;

class TelemarketingJob extends Job {

    public $queue = 'telemarketing2';
    public $tries = 3;
    public $jobId;

    public function __construct($jobId) {
        $this->jobId = $jobId;
    }

    public function handle() {
        $job = MarketingPhoneJob::model()->getOne($this->jobId);
        echo "Run Job " . $this->jobId . PHP_EOL;
        if (!$job) {
            return;
        }
        if ($job->status == MarketingPhoneJob::STATUS_RUNNING) {
            return;
        }
        $job->status = MarketingPhoneJob::STATUS_RUNNING;
        $job->save();
        $salers = $job->saler_ids;
        $salers = json_decode($salers, true);
        $task = CrmMarketingTask::model()->getOne($job->task_id);
        if (!$task) {
            return;
        }
        $task->other_assigned_total = 0;
        MerchantHelper::helper()->setMerchantId($task->merchant_id);
        if ($task->task_type != CrmMarketingTask::TYPE_PHONE) {
            return;
        }
        # jobCustomerType改为用户状态
        $customerStatus = $job->customer_type ? explode(",", $job->customer_type) : [];
        $list = \Admin\Services\Crm\MarketingServer::server()->getTaskCustomer($task, false);
        foreach ($list as $key => $customer) {
            if (MarketingPhoneLog::model()->newQuery()
                            ->where("merchant_id", MerchantHelper::helper()->getMerchantId())
                            ->where("customer_id", $customer->id)
                            ->where("call_status", MarketingPhoneLog::CALL_STATUS_INVALID_NUMBER)
                            ->where("call_result", MarketingPhoneLog::CALL_RESULT_QUIT_MARKETING)->exists()) {
                echo "CustomerID:{$customer->id} invalid number && quit marketing" . PHP_EOL;
                unset($list[$key]);
                continue;
            }
            if ($task->settle_times) {
                $settleTimes = CustomerStatus::model()->newQuery()
                        ->where("merchant_id", MerchantHelper::helper()->getMerchantId())
                        ->where("customer_id", $customer->id)
                        ->where("settle_times", ">=", $task->settle_times)
                        ->exists();
                if (!$settleTimes) {
                    echo "CustomerID:{$customer->id} Settle times mismatching " . PHP_EOL;
                    unset($list[$key]);
                    continue;
                }
            }
            if (is_array($customerStatus) && count($customerStatus)) {
                $existsCustomerStatus = CustomerStatus::model()->newQuery()
                        ->where("merchant_id", MerchantHelper::helper()->getMerchantId())
                        ->where("customer_id", $customer->id)
                        ->whereIn("status", $customerStatus)
                        ->exists();
                if (!$existsCustomerStatus) {
                    echo "CustomerID:{$customer->id} status mismatching " . PHP_EOL;
                    unset($list[$key]);
                    continue;
                }
            }
            if ($customer->type != $task->customer_type) {
                echo "CustomerType:{$customer->id} Type mismatch" . PHP_EOL;
                unset($list[$key]);
                continue;
            }

            if ($task->phone_time_interval) {
                $intervalTime = date("Y-m-d H:i:s", time() - 86400 * $task->phone_time_interval);
                if (MarketingPhoneAssign::model()->query()
                                ->where("customer_id", $customer->id)
                                ->where("status", '<>', MarketingPhoneAssign::STATUS_NORMAL)
                                ->where("merchant_id", MerchantHelper::helper()->getMerchantId())
                                ->where("assign_time", ">", $intervalTime)
                                ->exists()) {
                    echo "Customer:{$customer->id} Interval {$task->phone_time_interval} days." . PHP_EOL;
                    unset($list[$key]);
                    continue;
                }
            }

            if (MarketingPhoneAssign::model()->query()->where("customer_id", $customer->id)->where("status", MarketingPhoneAssign::STATUS_NORMAL)->where("merchant_id", MerchantHelper::helper()->getMerchantId())->exists()) {
                echo "Customer:{$customer->id} assigned" . PHP_EOL;
                $task->other_assigned_total++;
                unset($list[$key]);
                continue;
            }
        }
        # 分配数统计
        $assignNumber = [];
        foreach ($list as $key => $customer) {
            $i = $key % count($salers);
            $salerId = $salers[$i];
            echo $salerId . PHP_EOL;
            if (!isset($assignNumber[$salerId])) {
                $assignNumber[$salerId] = 0;
            }
            $job->per_capita_allocation = $job->per_capita_allocation ?: 0;
            # 超过人均分配量
            if ($assignNumber[$salerId] >= $job->per_capita_allocation && $job->per_capita_allocation > 0) {
                continue;
            }
            $attributes = [
                'merchant_id' => MerchantHelper::helper()->getMerchantId(),
                'customer_id' => $customer->id,
                'task_id' => $job->task_id,
                'saler_id' => $salerId,
                'assign_time' => DateHelper::dateTime(),
                'suggest_time' => $customer->suggest_time,
                'admin_id' => $job->admin_id,
            ];
            $res = MarketingPhoneAssign::model()->createModel($attributes);
            if ($res) {
                $assignNumber[$salerId]++;
            }
        }
        dispatch(new MarketingStatisticsJob($job->task_id));
        $job->status = MarketingPhoneJob::StATUS_OVER;
        $job->save();
        $task->save();
        echo ">>OVER<<";
    }

}
