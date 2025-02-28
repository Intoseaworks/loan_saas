<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Crm;

use Common\Models\Crm\CrmMarketingList;
use Common\Models\Crm\CrmMarketingBatch;
use Common\Models\Crm\CrmWhiteBatch;
use Common\Models\Crm\CrmWhiteList;
use Common\Models\Crm\Customer;
use Common\Models\Order\Order;
use Illuminate\Console\Command;

class BatchStatistics extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:batch:statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '批次统计';

    public function handle() {
        echo "==========marketing start" . PHP_EOL;
        $queryMarketing = CrmMarketingBatch::model()->newQuery();
//        $queryMarketing->where('indate', '>', date("Y-m-d H:i:s", time()+3599));
        $listMarketing = $queryMarketing->where("status", "1")->get();
        foreach ($listMarketing as $batchMarketing) {
            echo $batchMarketing->batch_number . "[id:{$batchMarketing->id}]" . PHP_EOL;
            # 处理有效数
            $query = CrmMarketingList::model()->newQuery();
            $batchMarketing->indate_count = $query->where("batch_id", $batchMarketing->id)
                            ->where('indate', '>', date("Y-m-d H:i:s"))
                            ->where("status", "1")->count();
            $sql = "SELECT count(1) as total,max(created_at) as last_time FROM crm_marketing_phone_log
WHERE task_id IN (select id from crm_marketing_task WHERE JSON_CONTAINS(batch_id, '{$batchMarketing->id}') AND customer_type='" . Customer::TYPE_MARKETING . "')";
            $res = \DB::select($sql);
            $batchMarketing->telephone_count = $res[0]->total;
            $batchMarketing->last_marketing_time = $res[0]->last_time > $batchMarketing->last_marketing_time ? $res[0]->last_time : $batchMarketing->last_marketing_time;
            $sql = "SELECT count(1) as total,max(created_at) as last_time FROM crm_marketing_sms_log
WHERE task_id IN (select id from crm_marketing_task WHERE JSON_CONTAINS(batch_id, '{$batchMarketing->id}') AND customer_type='" . Customer::TYPE_MARKETING . "')";
            $res = \DB::select($sql);
            $batchMarketing->sms_count = $res[0]->total;
            $batchMarketing->last_marketing_time = $res[0]->last_time > $batchMarketing->last_marketing_time ? $res[0]->last_time : $batchMarketing->last_marketing_time;
            #处理订单
            $sql = "SELECT task.batch_id, log.task_id,o.`status` as order_status,cutr.main_user_id,o.id as oid FROM crm_marketing_phone_log log
INNER JOIN crm_customer cutr ON cutr.id=log.customer_id
INNER JOIN crm_marketing_task task ON task.id=log.task_id
LEFT JOIN `order` o ON o.user_id=cutr.main_user_id AND log.created_at<o.created_at
WHERE task_id IN (select id from crm_marketing_task WHERE JSON_CONTAINS(batch_id, '{$batchMarketing->id}') AND customer_type='" . Customer::TYPE_MARKETING . "')
UNION ALL
SELECT task.batch_id, log.task_id,o.`status` as order_status,cutr.main_user_id,o.id as oid FROM crm_marketing_sms_log log
INNER JOIN crm_customer cutr ON cutr.id=log.customer_id
INNER JOIN crm_marketing_task task ON task.id=log.task_id
LEFT JOIN `order` o ON o.user_id=cutr.main_user_id AND log.created_at<o.created_at
WHERE task_id IN (select id from crm_marketing_task WHERE JSON_CONTAINS(batch_id, '{$batchMarketing->id}') AND customer_type='" . Customer::TYPE_MARKETING . "')";
            $res = \DB::select($sql);
            $finishCount = [];
            $regCount = [];
            foreach ($res as $item) {
                if ($item->main_user_id) {
                    if ($item->main_user_id) {
                        $regCount[] = $item->main_user_id;
                    }
                }
                if ($item->order_status && $item->order_status != Order::STATUS_CREATE) {
                    $finishCount[] = $item->oid;
                }
            }
            $regCount = array_unique($regCount);
            $batchMarketing->finish_count = count(array_unique($finishCount));
            $batchMarketing->reg_count = count($regCount);
            $batchMarketing->save();
        }
        echo "==========Whithlist start" . PHP_EOL;
        $queryWhite = CrmWhiteBatch::model()->newQuery();
//        $queryWhite->where('indate', '>', date("Y-m-d H:i:s", time() + 3599));
        $listWhite = $queryWhite->where("status", "1")->get();
        foreach ($listWhite as $batchWhite) {
            echo $batchWhite->batch_number . "[id:{$batchWhite->id}]" . PHP_EOL;
            $query = CrmWhiteList::model()->newQuery();
            $batchWhite->indate_count = $query->where("batch_id", $batchWhite->id)
                            ->where('indate', '>', date("Y-m-d H:i:s"))
                            ->where("status", "1")->count();
            $sql = "SELECT count(1) as total,max(created_at) as last_time FROM crm_marketing_phone_log
WHERE task_id IN (select id from crm_marketing_task WHERE JSON_CONTAINS(batch_id, '{$batchWhite->id}') AND customer_type='" . Customer::TYPE_WHITELIST . "')";
            $res = \DB::select($sql);
            $batchWhite->telephone_count = $res[0]->total;
            $batchWhite->last_marketing_time = $res[0]->last_time > $batchWhite->last_marketing_time ? $res[0]->last_time : $batchWhite->last_marketing_time;

            $sql = "SELECT count(1) as total,max(created_at) as last_time FROM crm_marketing_sms_log
WHERE task_id IN (select id from crm_marketing_task WHERE JSON_CONTAINS(batch_id, '{$batchWhite->id}') AND customer_type='" . Customer::TYPE_WHITELIST . "')";
            $res = \DB::select($sql);
            $batchWhite->sms_count = $res[0]->total;
            $batchWhite->last_marketing_time = $res[0]->last_time > $batchWhite->last_marketing_time ? $res[0]->last_time : $batchWhite->last_marketing_time;

            #处理订单
            $sql = "SELECT task.batch_id, log.task_id,o.`status` as order_status,cutr.main_user_id,o.id as oid FROM crm_marketing_phone_log log
INNER JOIN crm_customer cutr ON cutr.id=log.customer_id
INNER JOIN crm_marketing_task task ON task.id=log.task_id
LEFT JOIN `order` o ON o.user_id=cutr.main_user_id AND log.created_at<o.created_at
WHERE task_id IN (select id from crm_marketing_task WHERE JSON_CONTAINS(batch_id, '{$batchWhite->id}') AND customer_type='" . Customer::TYPE_WHITELIST . "')
UNION ALL
SELECT task.batch_id, log.task_id,o.`status` as order_status,cutr.main_user_id,o.id as oid FROM crm_marketing_sms_log log
INNER JOIN crm_customer cutr ON cutr.id=log.customer_id
INNER JOIN crm_marketing_task task ON task.id=log.task_id
LEFT JOIN `order` o ON o.user_id=cutr.main_user_id AND log.created_at<o.created_at
WHERE task_id IN (select id from crm_marketing_task WHERE JSON_CONTAINS(batch_id, '{$batchWhite->id}') AND customer_type='" . Customer::TYPE_WHITELIST . "')";
            $res = \DB::select($sql);
            $finishCount = [];
            $regCount = [];
            foreach ($res as $item) {

                if ($item->main_user_id) {
                    $regCount[] = $item->main_user_id;
                }
                if ($item->order_status && $item->order_status != Order::STATUS_CREATE) {
                    $finishCount[] = $item->oid;
                }
            }
            $regCount = array_unique($regCount);
            $batchWhite->finish_count = count(array_unique($finishCount));
            $batchWhite->reg_count = count($regCount);
            $batchWhite->save();
        }
    }

}
