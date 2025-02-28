<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Crm;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Common\Models\Crm\MarketingPhoneReport;
use Common\Models\Crm\MarketingPhoneLog;
use Common\Models\Crm\MarketingPhoneAssign;
use Common\Models\Order\Order;

class MarketingReport extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:marketing:report  {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '营销报表 {date YYYY-mm-dd}';

    public function handle() {
        /** 扫描用户表 */
        if ($date = $this->argument('date')) {
            $this->maker($date);
            exit();
        }
        $startDate = time() - 86400 * 7;
        for ($i = $startDate; $i <= time(); $i += 86400) {
            $this->maker(date("Y-m-d", $i));
        }
        echo "End";
    }

    private function maker($date) {
        echo $date . PHP_EOL;
        $sql = "select admin.merchant_id,task_id,saler_id,admin.nickname,count(1) total from crm_marketing_phone_assign assign
INNER JOIN staff admin ON assign.saler_id=admin.id
INNER JOIN crm_customer customer ON customer.id=assign.customer_id
WHERE date(assign.assign_time)='{$date}'
group by saler_id,task_id;";
        $salers = DB::select($sql);
        $result = [];
        foreach ($salers as $saler) {
            $customer = [];
            $res = [
                "merchant_id" => $saler->merchant_id,
                "report_date" => $date,
                "saler_id" => $saler->saler_id,
                "task_id" => $saler->task_id,
//                "customer_type" => $saler->type,
                "total" => $saler->total,
                "agree_number" => 0,
                "call_reject_number" => 0,
                "neutral_number" => 0,
                "miss_call_number" => 0,
                "wrong_number" => 0,
                "apply_number" => 0,
                "pass_number" => 0,
                "reject_number" => 0,
            ];
            $logSql = "select log.*,assign.`status` as assign_status,assign.stop_reason from crm_marketing_phone_log log
INNER JOIN crm_marketing_phone_assign assign ON log.assign_id=assign.id
WHERE date(assign.assign_time)='{$date}' AND saler_id={$saler->saler_id} AND log.task_id={$saler->task_id}";
            $logs = DB::select($logSql);
            foreach ($logs as $log) {
                if (in_array($log->call_result, [
                    MarketingPhoneLog::CALL_RESULT_WILL_APPLY,
                    MarketingPhoneLog::CALL_RESULT_OTHER_PERSON_USE_PHONE_AND_WILL_APPLY
                ])) {
                    $res['agree_number'] += 1;
                }
                if (in_array($log->call_result, [
//                    MarketingPhoneLog::CALL_RESULT_HANG_UP,
//                    MarketingPhoneLog::CALL_RESULT_THINK_ABOUT_IT,//call_result =2和3，也计算为拒绝数里面tracy20210610
                    MarketingPhoneLog::CALL_RESULT_QUIT_MARKETING,
                    MarketingPhoneLog::CALL_RESULT_OTHER_PERSON_USE_PHONE_AND_HAVE_NO_INTENTION_TO_APPLY,
                    MarketingPhoneLog::CALL_RESULT_NO_INTENTION_TO_APPLY
                ])) {
                    $res['call_reject_number'] += 1;
                }
                //call_result =2和3，计算为中立数tracy20210809
                if (in_array($log->call_result, [
                    MarketingPhoneLog::CALL_RESULT_HANG_UP,
                    MarketingPhoneLog::CALL_RESULT_THINK_ABOUT_IT
                ])) {
                    $res['neutral_number'] += 1;
                }
//                if (in_array($log->call_status, [
//                            MarketingPhoneLog::CALL_STATUS_NO_ANSWER,
//                            MarketingPhoneLog::CALL_STATUS_NO_SERVICE
//                        ])) {
                if (in_array($log->call_result, [
                    MarketingPhoneLog::CALL_RESULT_NO_ANSWER,
                    MarketingPhoneLog::CALL_RESULT_NO_SERVICE
                ])) {
                    $res['miss_call_number'] += 1;
                }
//                if (in_array($log->call_status, [
//                            MarketingPhoneLog::CALL_STATUS_INVALID_NUMBER,
//                            MarketingPhoneLog::CALL_STATUS_WRONG_NUMBER
//                        ])) {
                if (in_array($log->call_result, [
                    MarketingPhoneLog::CALL_RESULT_INVALID_NUMBER,
                    MarketingPhoneLog::CALL_RESULT_WRONG_NUMBER
                ])) {
                    $res['wrong_number'] += 1;
                }
                if ($log->assign_status == MarketingPhoneAssign::STATUS_FORGET) {
                    $customer[] = $log->customer_id;
                }
            }
            if ($customer) {
//                dd($customer);
                $orderSql = "select o.*,cutr_status.customer_id from `order` o
INNER JOIN crm_customer_status cutr_status ON o.user_id=cutr_status.main_user_id AND cutr_status.merchant_id={$saler->merchant_id}
WHERE o.`status` <>'create' AND o.signed_time>'{$date}' AND cutr_status.customer_id IN(" . implode(',', $customer) . ") AND o.merchant_id={$saler->merchant_id}";
                $orders = DB::select($orderSql);
                $res['apply_number'] = 0;
                foreach ($orders as $order) {
                    $maxAssignTime = $this->maxAssignDate($order->customer_id, $order->merchant_id, $date, $saler->task_id);
                    if ($maxAssignTime == null || ($maxAssignTime > $order->signed_time)) {
                        list($minLogTime, $assignTime) = $this->minLogTime($order->customer_id, $order->merchant_id, $saler->saler_id, $saler->task_id);
                        $fiveDayLogTime = date("Y-m-d H:i:s", strtotime($minLogTime) + 5 * 86400);
                        if($assignTime <= $order->signed_time && $fiveDayLogTime>=$order->signed_time){
                            //echo "$saler->saler_id --- $order->signed_time === $minLogTime === $fiveDayLogTime --- $maxAssignTime";
                            $res['apply_number']++;
                            if (in_array($order->status, array_merge(Order::STATUS_APPROVE_PASS, [Order::STATUS_FINISH]))) {
                                $res['pass_number'] += 1;
                            }
                            if (in_array($order->status, array_merge(Order::APPROVAL_REJECT_STATUS))) {
                                $res['reject_number'] += 1;
                            }
                        }
                    }
                }
            }
            MarketingPhoneReport::model()->updateOrCreateModel($res, ["report_date" => $res['report_date'], "saler_id" => $res['saler_id'], "task_id" => $res['task_id']]);
        }
    }

    /**
     * 获取未来最近的一次分配记录
     * @param type $customerId
     * @param type $merchantId
     * @param type $date
     */
    public function maxAssignDate($customerId, $merchantId, $date, $taskId) {
        $sql = "select min(assign_time) as min_assign_date from crm_marketing_phone_assign WHERE customer_id='{$customerId}' AND merchant_id='{$merchantId}' AND assign_time>'{$date} 23:59:59' AND task_id={$taskId}";
        $minAssignDate = DB::select($sql);
        return $minAssignDate[0]->min_assign_date ?? null;
    }

    public function minLogTime($customerId, $merchantId, $salerId, $taskId) {
        //$sql = "select max(created_at) as max_log_date from crm_marketing_phone_log where customer_id={$customerId} AND merchant_id='{$merchantId}' AND operator_id={$salerId} AND task_id={$taskId}";
        $sql = "select max(log.created_at) as max_log_date,min(assign.assign_time) as assign_time from crm_marketing_phone_log log inner join crm_marketing_phone_assign assign ON assign.id=log.assign_id where log.customer_id={$customerId} AND log.merchant_id='{$merchantId}' AND log.operator_id={$salerId} AND log.task_id={$taskId}";
        $maxLogTime = DB::select($sql);
        return [$maxLogTime[0]->max_log_date ?? null, $maxLogTime[0]->assign_time ?? null];
    }
}
