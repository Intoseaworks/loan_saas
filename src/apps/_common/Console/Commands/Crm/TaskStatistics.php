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
use Common\Models\Crm\Customer;
use Common\Models\Crm\MarketingPhoneAssign;
use Common\Models\Order\Order;
use Illuminate\Console\Command;
use Common\Jobs\Crm\MarketingStatisticsJob;

class TaskStatistics extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:task:statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '任务统计';

    public function handle() {
        echo "==========Task start" . PHP_EOL;
        $queryTask = CrmMarketingTask::model()->newQuery();
        $listTask = $queryTask->where("status", "1")->get();
        foreach ($listTask as $task) {
            echo "[{$task->id}]".$task->task_name.PHP_EOL;
            $success_total = $send_total = $paid_total = 0;
            $apply_total = $signed_total = $agree_total = [];
            switch ($task->task_type) {
                #短信类
                case CrmMarketingTask::TYPE_SMS:
                    $sql = "SELECT task.*,cs.main_user_id,o.`status` as order_status,o.signed_time,o.pass_time,o.id as oid,log.status as log_status FROM crm_marketing_sms_log log
INNER JOIN crm_marketing_task task ON task.id=log.task_id
INNER JOIN crm_customer cutr ON cutr.id=log.customer_id
INNER JOIN crm_customer_status cs ON cs.customer_id=log.customer_id AND cs.merchant_id=log.merchant_id
LEFT JOIN `order` o ON o.user_id=cs.main_user_id AND log.created_at<o.created_at
WHERE task.id='{$task->id}'";
                    break;
                #电销类
                case CrmMarketingTask::TYPE_PHONE:
                    $sql = "SELECT task.*,cs.main_user_id,o.`status` as order_status,o.signed_time,o.pass_time,o.id as oid,1 as log_status FROM crm_marketing_phone_log log
INNER JOIN crm_marketing_task task ON task.id=log.task_id
INNER JOIN crm_customer cutr ON cutr.id=log.customer_id
INNER JOIN crm_customer_status cs ON cs.customer_id=log.customer_id AND cs.merchant_id=log.merchant_id
LEFT JOIN `order` o ON o.user_id=cs.main_user_id AND log.created_at<o.created_at
WHERE task.id='{$task->id}'";
                    $task->phone_finish_total = MarketingPhoneAssign::model()->newQuery()->where("task_id", $task->id)->where("status", "<>", "1")->count();
                    dispatch(new MarketingStatisticsJob($task->id));
                    break;
            }
            if ($sql) {
                $list = \DB::select($sql);
                $send_total = count($list);
                $success_total = 0;
                foreach ($list as $record) {
                    if (in_array($record->order_status, Order::CONTRACT_STATUS)) {
                        $paid_total++;
                    }
                    if ($record->order_status) {
                        $apply_total[] = $record->oid;
                    }
                    if ($record->signed_time) {
                        $signed_total[] = $record->main_user_id;
                    }
                    if ($record->pass_time) {
                        $agree_total[] = $record->main_user_id;
                    }
                    if($record->log_status == 1){
                        $success_total++;
                    }
                }
                $task->success_total = $success_total;
                $task->send_total = $send_total;
                $task->paid_total = $paid_total;
                $task->apply_total = count(array_unique($apply_total));
                $task->signed_total = count(array_unique($signed_total));
                $task->agree_total = count(array_unique($agree_total));
                $task->save();
                echo "phone_total=" . $task->phone_total.PHP_EOL;
            }
        }
    }

}
