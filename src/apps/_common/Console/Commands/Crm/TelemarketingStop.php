<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Crm;

use Common\Models\Crm\CrmMarketingTask;
use Common\Models\Crm\CrmMarketingBatch;
use Common\Models\Crm\CrmWhiteBatch;
use Common\Models\Crm\CrmWhiteList;
use Common\Models\Crm\MarketingPhoneLog;
use Common\Models\Order\Order;
use Common\Models\Crm\MarketingPhoneAssign;
use Illuminate\Console\Command;

class TelemarketingStop extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:telemarketing:stop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '电销任务停止';

    public function handle() {
        echo "==========TaskStop start" . PHP_EOL;
        $query = MarketingPhoneAssign::model()->newQuery();
        $listTelemarketing = $query
                ->where("status", "1")
                ->get();
        foreach ($listTelemarketing as $telemarketing) {
            $task = CrmMarketingTask::model()->getOne($telemarketing->task_id);
            $customer = $telemarketing->customer;
            $customerStatus = \Common\Models\Crm\CustomerStatus::model()->where("merchant_id", $telemarketing->merchant_id)->where("customer_id", $telemarketing->customer_id)->first();
            if(!isset($customer->telephone)){
                print_r($telemarketing->id);
                continue;
            }
            echo ($customer->telephone ?? "无手机号") . PHP_EOL;
            $stop = false;
            $stopTerm = [];
            if ($task->phone_stop_term) {
                $stopTerm = json_decode($task->phone_stop_term, true);
            }

            if (in_array(CrmMarketingTask::STOP_PHONE_APPLY, $stopTerm) && isset($customerStatus->main_user_id)) {
                $orderCount = Order::model()->newQuery()->where("user_id", $customerStatus->main_user_id)->where("signed_time", ">", $telemarketing->created_at)->count();
                if ($orderCount > 0) {
                    echo $telemarketing->stop_reason = "用户完件";
                    $telemarketing->status = CrmMarketingTask::STATUS_FORGET;
                    $telemarketing->save();
                }
            }
            if (in_array(CrmMarketingTask::STOP_PHONE_STATUS_CHANGE, $stopTerm) && isset($customerStatus->status)) {
                $lastTele = MarketingPhoneLog::model()->newQuery()->where("assign_id", $telemarketing->id)->orderByDesc("id")->first();
                if ($lastTele && $lastTele->customer_status != $customerStatus->status) {
                    echo $telemarketing->stop_reason = "客户状态变更";
                    $telemarketing->status = CrmMarketingTask::STATUS_FORGET;
                    $telemarketing->save();
                }
            }
            if (in_array(CrmMarketingTask::STOP_PHONE_USER_REJECT, $stopTerm)) {
                $userReject = MarketingPhoneLog::model()->newQuery()->where("assign_id", $telemarketing->id)
                        ->where("call_refusal", ">=", "1")
                        ->exists();
                if ($userReject) {
                    echo $telemarketing->stop_reason = "用户拒绝";
                    $telemarketing->status = CrmMarketingTask::STATUS_FORGET;
                    $telemarketing->save();
                }
            }
            #进入电销天数
            if (in_array(CrmMarketingTask::STOP_PHONE_ENTER_TEL_DAYS, $stopTerm)) {
                $enterDays = round((time() - strtotime($telemarketing->created_at->toDateTimeString())) / 86400);
                if (intval($enterDays) > intval($task->phone_stop_term_detail)) {
                    echo $telemarketing->stop_reason = "超出进入电销天数";
                    $telemarketing->status = CrmMarketingTask::STATUS_FORGET;
                    $telemarketing->save();
                }
            }

            $sql = "SELECT
	count( 1 ) c 
FROM
	(
SELECT
	*,
	(
SELECT
	TIMESTAMPDIFF( HOUR, created_at, clog.created_at ) 
FROM
	crm_marketing_phone_log 
WHERE
	customer_id = clog.customer_id 
	AND task_id=clog.task_id
	AND id < clog.id 
ORDER BY
	id DESC 
	LIMIT 1 
	) AS diffhour 
FROM
	crm_marketing_phone_log clog 
WHERE
	customer_id = {$customer->id} 
	AND task_id={$telemarketing->task_id} 
	AND call_result in (2, 9, 10, 11, 12)
	) a 
WHERE
	diffhour >=1";
            $res = \DB::select($sql);
            if ($res[0]->c >= 2) {
                $telemarketing->status = MarketingPhoneAssign::STATUS_FORGET;
                echo $telemarketing->stop_reason = "NO ANSWER 3 Times";
                $telemarketing->save();
            }
        }
    }

}
