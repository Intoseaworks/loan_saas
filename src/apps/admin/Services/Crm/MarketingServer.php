<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Crm;

use Admin\Imports\Crm\MarketingListImport;
use Admin\Services\BaseService;
use Common\Models\Crm\CrmMarketingBatch;
use Common\Models\Crm\CrmMarketingList;
use Common\Models\Crm\CrmMarketingTask;
use Common\Models\Crm\CrmWhiteFailed;
use Common\Models\Crm\CrmWhiteList;
use Common\Models\Crm\Customer;
use Common\Models\Risk\RiskBlacklist;
use Common\Utils\LoginHelper;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Common\Models\Order\Order;
use Illuminate\Support\Facades\DB;
use Admin\Models\User\UserBlack;
use Common\Jobs\Crm\MarketingSmsJob;
use Common\Utils\MerchantHelper;
use Common\Jobs\Crm\MarketingStatisticsJob;

class MarketingServer extends BaseService {

    const EXCEL_RULE = [
        'telephone' => 'required|mobile',
        'type' => 'required',
    ];

    public function upload($batchInfo) {
        $res = Excel::toArray(new MarketingListImport, $batchInfo['file']);
        //作废老的同名批次
        if (CrmMarketingBatch::model()->newQuery()->where("batch_number", $batchInfo['batch_number'])->exists()) {
            return $this->outputError("Batch number already exist!");
        }
        $indate = date("Y-m-d H:i:s", time() + 86400 * $batchInfo['days']);
        $batch = CrmMarketingBatch::model()->createModel([
            "merchant_id" => MerchantHelper::getMerchantId(),
            "batch_number" => $batchInfo['batch_number'],
            "indate" => $indate,
            "match_rule" => $batchInfo['match_rule'],
            "total_count" => 0,
            'admin_id' => LoginHelper::getAdminId()
        ]);
        dispatch(new \Common\Jobs\Crm\UploadMarketingJob($batch, $res, LoginHelper::getAdminId()));
        return $this->outputSuccess("success " . count($res[0]) . "!");
    }

    public function badUser($telephone, $rule) {
        if (in_array(1, $rule)) {
            $sql = "select * from `user` u
INNER JOIN `order` o ON u.id=o.user_id
WHERE u.merchant_id='" . MerchantHelper::getMerchantId() . "' AND u.telephone='{$telephone}' AND o.`status` in('" . implode("','", Order::STATUS_NOT_COMPLETE) . "');";
            return DB::select($sql);
        }
        return [];
    }

    public function setBatchStatus($params) {
        switch ($params['action']) {
            case "delete":
                CrmMarketingBatch::model()->newQuery()->whereIN("id", $params['id_list'])->update(['status' => CrmMarketingBatch::STATUS_FORGET]);
                break;
            case "using":
                CrmMarketingBatch::model()->newQuery()->whereIN("id", $params['id_list'])->update(['status' => CrmMarketingBatch::STATUS_NORMAL]);
                break;
        }
        return $this->outputSuccess();
    }

    public function setMarketingListStatus($params) {
        switch ($params['action']) {
            case "delete":
                CrmMarketingList::model()->newQuery()->whereIN("id", $params['id_list'])->update(['status' => CrmMarketingBatch::STATUS_FORGET]);
                break;
            case "using":
                CrmMarketingList::model()->newQuery()->whereIN("id", $params['id_list'])->update(['status' => CrmMarketingBatch::STATUS_NORMAL]);
                break;
        }
        return $this->outputSuccess();
    }

    /**
     *
     * @param type $params
     */
    public function setBatchPostpone($params) {
//        $indate = date("Y-m-d H:i:s", time() + 86400 * $params['days']);
        CrmMarketingBatch::model()->newQuery()->whereIN("id", $params['id_list'])->update(['indate' => DB::raw("date_add(indate, INTERVAL {$params['days']} DAY)")]);
        CrmMarketingList::model()->newQuery()->whereIN("batch_id", $params['id_list'])->update(['indate' => DB::raw("date_add(indate, INTERVAL {$params['days']} DAY)")]);
        return $this->outputSuccess();
    }

    public function setPostpone($params) {
//        $indate = date("Y-m-d H:i:s", time() + 86400 * $params['days']);
        CrmMarketingList::model()->newQuery()->whereIN("id", $params['id_list'])->update(['indate' => DB::raw("date_add(indate, INTERVAL {$params['days']} DAY)")]);
        return $this->outputSuccess();
    }

    public function list($params) {
        $size = array_get($params, 'page_size', 10);
        $query = CrmMarketingList::model()->newQuery()->where("merchant_id", MerchantHelper::getMerchantId());
        if (isset($params['batch_number']) && $params['batch_number']) {
            $keyword = $params['batch_number'];
            $query->whereHas('crmMarketingBatch', function ($batch) use ($keyword) {
                $batch->where(function ($batch) use ($keyword) {
                    $keyword = trim($keyword);
                    $batch->where('batch_number', 'like', '%' . $keyword . '%');
                });
            });
        }
        if (isset($params['customer_type'])) {
            $query->where("type", $params['customer_type']);
        }
        if (isset($params['status'])) {
            $query->where("status", $params['status']);
        }
        if (isset($params['telephone'])) {
            $query->where("telephone", $params['telephone']);
        }
        if (isset($params['admin'])) {
            $query->where("admin_id", $params['admin']);
        }
        $query->orderByDesc("id");
        $res = $query->paginate($size);
        foreach ($res as $item) {
            $item->batch_number = $item->crmMarketingBatch->batch_number;
            $item->status_txt = t(CrmMarketingList::STATUS[$item->status], "crm");
            $item->customer_status_txt = isset($item->customerStatus()->status) ? t(Customer::STATUS_CUSTOMER[$item->customerStatus()->status], "crm") : "";
            $item->operator_name = $item->operator->nickname;
        }
        return $res;
    }

    public function taskList($params) {

        $size = array_get($params, 'page_size', 10);
        $query = CrmMarketingTask::model()->newQuery()->where("merchant_id", MerchantHelper::getMerchantId());
        if (isset($params['task_name'])) {
            $query->where("task_name", 'like', "%{$params['task_name']}%");
        }
        if (isset($params['task_status'])) {
            $query->where("status", $params['task_status']);
        }
        if (isset($params['task_type'])) {
            $query->where("task_type", $params['task_type']);
        }
        $query->orderByDesc("id");
        $res = $query->paginate($size);
        foreach ($res as $item) {
            $item->apply_total_rate = $item->success_total ? round($item->apply_total / $item->success_total * 100, 2) : "-";
            $item->apply_pass_rate = $item->apply_total ? round($item->paid_total / $item->apply_total * 100, 2) : "-";
            $item->paid_rate = $item->success_total ? round($item->paid_total / $item->success_total * 100, 2) : "-";
        }
        return $res;
    }

    public function report($params) {
//        $where = " 1 ";
//        if (isset($params['start_date']) && $params['start_date']) {
//            $where .= " AND report_date>='{$params['start_date']}'";
//        }
//        if (isset($params['end_date']) && $params['end_date']) {
//            $where .= " AND report_date<='{$params['end_date']}'";
//        }
//        if (isset($params['saler_id']) && $params['saler_id']) {
//            $where .= " AND saler_id='{$params['saler_id']}'";
//        }
//        if (isset($params['task_name']) && $params['task_name']) {
//            $where .= " AND task_id='{$params['task_name']}'";
//        }
//        $dateName = "";
//        switch ($params['date_type']) {
//            case "year":
//                $dateName = "left(report_date,4)";
//                break;
//            case "month":
//                $dateName = "concat(left(report_date,4),'-',Left(MONTHNAME(rpt.report_date),3))";
//                break;
//            case "week":
//                $dateName = "concat(left(report_date,4),'#',date_format(report_date,'%U'))";
//                break;
//            default:
//                $dateName = "report_date";
//        }
//        $sql = "SELECT
//rpt.saler_id,
//stf.username as saler_name,
//{$dateName} as areport_date,
//SUM(total) as total,
//SUM(agree_number) as agree_number,
//SUM(call_reject_number) as call_reject_number,
//SUM(miss_call_number) as miss_call_number,
//SUM(wrong_number) as wrong_number,
//SUM(apply_number) as apply_number,
//SUM(pass_number) as pass_number,
//SUM(reject_number) as reject_number
//FROM crm_marketing_phone_report rpt
//INNER JOIN staff stf ON rpt.saler_id=stf.id AND stf.merchant_id='".MerchantHelper::helper()->getMerchantId()."'
//WHERE {$where}
//GROUP BY saler_id,areport_date
//ORDER BY areport_date DESC,total DESC";
//        $res = DB::select($sql);
//        foreach ($res as $item) {
//            $item->report_date = $item->areport_date;
//            $item->agree_number_rate = round($item->agree_number / $item->total * 100, 2) . "%";
//            $item->call_reject_rate = round($item->call_reject_number / $item->total * 100, 2) . "%";
//            $item->miss_call_rate = round($item->miss_call_number / $item->total * 100, 2) . "%";
//            $item->wrong_number_rate = round($item->wrong_number / $item->total * 100, 2) . "%";
//            $item->apply_number_rate = round($item->apply_number / $item->total * 100, 2) . "%";
////            $item->pass_number_rate = round($item->pass_number / $item->total * 100, 2) . "%";
////            $item->conv_rate = $item->apply_number == 0 ? 0 : round(($item->total - $item->reject_number) / $item->total * 100, 2) . "%";
//            $item->conv_rate = $item->pass_number ? round($item->pass_number / $item->total * 100, 2) . "%" : "--";
//            $item->pass_number_rate = $item->pass_number ? round($item->pass_number / $item->apply_number * 100, 2) . "%" : "--";
//        }
//        if ($this->getExport()) {
//            TelemarketingExport::getInstance()->csvArray($res, TelemarketingExport::SCENE_EXPORT);
//        }
//        return $res;
    }

    public function taskRecords($params) {
        $where = " merchant_id='" . MerchantHelper::getMerchantId() . "' ";
        $pageSize = array_get($params, 'page_size', 10);
        $currentPage = array_get($params, 'page', 1);
        if (isset($params['task_name'])) {
            $where .= " AND task_name LIKE '%{$params['task_name']}%'";
        }
        if (isset($params['task_type'])) {
            $where .= " AND task_type = '{$params['task_type']}'";
        }
        if (isset($params['task_status'])) {
            $where .= " AND task_status = '{$params['task_status']}'";
        }
        if (isset($params['task_result'])) {
            $where .= " AND task_result = '{$params['task_result']}'";
        }
        if (isset($params['page_size']) && $params['page_size']) {
            $pageSize = $params['page_size'];
        }
        if (isset($params['page']) && $params['page']) {
            $currentPage = $params['page'];
        }
        $limit = " LIMIT " . (($currentPage - 1) * $pageSize) . ", {$pageSize};";
        $sql = "select * from (select task.merchant_id,task.task_name,task.task_type, task.status as task_status, cutr.telephone,plog.customer_id,plog.created_at,plog.remark,
1 as task_result,staff.nickname as operator_name from crm_marketing_phone_log plog
INNER JOIN crm_marketing_task task ON plog.task_id=task.id
INNER JOIN crm_customer cutr ON cutr.id=plog.customer_id
INNER JOIN staff ON staff.id=plog.operator_id
UNION ALL
select task.merchant_id,task.task_name,task.task_type, task.status as task_status,slog.telephone,slog.customer_id,slog.created_at,'' as remark,slog.status as task_result,staff.nickname as operator_name from crm_marketing_sms_log slog
INNER JOIN crm_marketing_task task ON slog.task_id=task.id
INNER JOIN staff ON staff.id=task.admin_id)
as a WHERE {$where} ORDER BY created_at DESC";
        $res = DB::select($sql);
        $data = [];
        $data['total'] = count($res);
        $data['list'] = DB::select($sql . $limit);
        $data['page_total'] = ceil($data['total'] / $pageSize);
        $data['current_page'] = $currentPage;
        $data['page_size'] = $pageSize;
        return $data;
    }

    public function listBatch($params) {
        $size = array_get($params, 'page_size', 10);
        $query = CrmMarketingBatch::model()->newQuery()->where("merchant_id", MerchantHelper::getMerchantId());
        if (isset($params['batch_number'])) {
            $query->where("batch_number", "LIKE", "%{$params['batch_number']}%");
        }
        if (isset($params['import_date'])) {
            $query->whereBetween("created_at", [$params['import_date'] . ' 00:00:00', $params['import_date'] . ' 23:59:59']);
        }
        if (isset($params['admin'])) {
            $query->where("admin_id", $params['admin']);
        }
        if ($size == 100000) {
            $query->where("indate_count", ">", 0);
        }
        $query->orderByDesc("id");
        $res = $query->paginate($size);
        foreach ($res as $item) {
            $item->operator_name = $item->operator->nickname ?? "";
        }
        return $res;
    }

    public function checkData($item) {
        $res = Validator::make(
                        $item,
                        self::EXCEL_RULE
        );
        return $res;
    }

    public function checkBlackList($item, $rule) {
        if ($rule) {
            $where = false;
            $query = RiskBlacklist::model()->newQuery();
            if (in_array(1, $rule)) {
                $where = true;
                $query->where("value", $item['telephone']);
            }
            if (isset($item['email']) && in_array(2, $rule)) {
                $where = true;
                $query->orWhere("value", $item['email']);
            }
            if ($where) {
                return $query;
            } else {
                return $query->where("id", "-1");
            }
        }
        return [];
    }

    /**
     * 入库customer
     * @param CrmWhiteList $marketingList
     */
    public function intoCustomer(CrmMarketingList $marketingList) {
        $query = Customer::model()->newQuery();
        $wheres = [
            "id_number" => ['id_type' => $marketingList->id_type, "id_number" => $marketingList->id_number],
            "fullname" => ["telephone" => $marketingList->telephone, "fullname" => $marketingList->fullname],
            "birthday" => ["birthday" => $marketingList->birthday, "fullname" => $marketingList->fullname],
            "telephone" => ["telephone" => $marketingList->telephone],
            "email" => ["email" => $marketingList->email],
        ];
        $attributes = [
            "telephone" => $marketingList->telephone,
            "email" => $marketingList->email,
            "fullname" => $marketingList->fullname,
            "birthday" => $marketingList->birthday,
            "id_type" => $marketingList->id_type,
            "id_number" => $marketingList->id_number,
            "batch_id" => $marketingList->batch_id,
            "type" => Customer::TYPE_MARKETING,
            "remark" => $marketingList->remark
        ];
        $isSave = true;
        foreach ($wheres as $key => $where) {
            if ($marketingList->$key) {
                $query = Customer::model()->newQuery();
                $res = $query->where($where)->get()->first();
                if ($res) {
                    $isSave = false;
                    $marketingList->customer_id = $res->id;
                    $marketingList->save();
                    \Common\Models\Crm\CustomerStatus::model()->updateOrCreateModel([
                        "merchant_id" => MerchantHelper::getMerchantId(),
                        "customer_id" => $res->id,
                        "batch_id" => $marketingList->batch_id,
                        "type" => Customer::TYPE_MARKETING,
                        "remark" => $marketingList->remark
                            ], [
                        "merchant_id" => MerchantHelper::getMerchantId(),
                        "customer_id" => $res->id,
                    ]);
                    Customer::model()->updateOrCreateModel($attributes, $where);
                    break;
                }
            }
        }
        if ($isSave) {
            $customer = Customer::model()->createModel($attributes);
            \Common\Models\Crm\CustomerStatus::model()->updateOrCreateModel([
                "merchant_id" => MerchantHelper::getMerchantId(),
                "customer_id" => $customer->id,
                "batch_id" => $marketingList->batch_id,
                "type" => Customer::TYPE_MARKETING,
                "remark" => $marketingList->remark
                    ], [
                "merchant_id" => MerchantHelper::getMerchantId(),
                "customer_id" => $customer->id,
            ]);
            $marketingList->customer_id = $customer->id;
            $marketingList->save();
        }
    }

    public function saveTask($attributes) {
        $attributes['admin_id'] = LoginHelper::getAdminId();
        $attributes['merchant_id'] = MerchantHelper::getMerchantId();
        unset($attributes['token']);
        if (isset($attributes["id"]) && $attributes["id"]) {
            $id = $attributes["id"];
            unset($attributes["id"]);

            $res = CrmMarketingTask::model()->updateOrCreateModel($attributes, ['id' => $id]);
        } else {
            if (CrmMarketingTask::model()->newQuery()->where("task_name", $attributes['task_name'])->exists()) {
                return $this->outputException("任务已存在");
            }
            $res = CrmMarketingTask::model()->createModel($attributes);
            if ($res->send_time == '') {
                $job = new MarketingSmsJob($res->id);
                $job->queue = $job->queue . "-" . $res->merchant_id;
                dispatch($job);
//                dispatch(new MarketingSmsJob($res->id));
            }
            # 计算可分配总数
            dispatch(new MarketingStatisticsJob($res->id));
        }
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    public function calConditionCustomers($params) {
        $query = Customer::model()->newQuery();
        $query->join("crm_customer_status", function($join) {
            $join->on("crm_customer_status.customer_id", "=", "crm_customer.id");
            $join->where("crm_customer_status.merchant_id", '=', MerchantHelper::getMerchantId());
        });
//        $query->select(\DB::raw("`crm_customer_status`.clm_level,"
//            . "`crm_customer_status`.type,"
//            . "`crm_customer_status`.status,"
//            . "`crm_customer_status`.batch_id,"
//            . "`crm_customer_status`.suggest_time,"
//            . "`crm_customer_status`.last_login_time,"
//            . "`crm_customer_status`.max_overdue_days,"
//            . "`crm_customer_status`.status_stop_days,"
//            . "`crm_customer_status`.settle_times,"
//            . "`crm_customer_status`.last_settle_time,"
//            . "`crm_customer_status`.status_updated_time,"
//            . "crm_customer_status.remark,"
//            . "crm_customer_status.merchant_id,"
//            . "crm_customer.id,"
//            . "crm_customer.created_at,"
//            . "crm_customer.telephone,"
//            . "crm_customer.telephone_status,"
//            . "crm_customer.telephone_check_time,"
//            . "crm_customer.email,"
//            . "crm_customer.fullname,"
//            . "crm_customer.birthday,"
//            . "crm_customer.id_type,"
//            . "crm_customer.id_number,"
//            . "crm_customer.gender"));
        #处理等级
        $clmLevel = isset($params['clm_level']) ? json_decode($params['clm_level'], true) : [];
        if (is_array($clmLevel) && count($clmLevel)) {
            $query->whereIn("crm_customer_status.clm_level", $clmLevel);
        }
        #处理用户状态
        $customerStatus = isset($params['customer_status']) ? json_decode($params['customer_status'], true) : [];
        if (is_array($customerStatus) && count($customerStatus)) {
            $query->whereIn("crm_customer_status.status", $customerStatus);
        }
        #用户群体
        $query->where("crm_customer_status.type", $params['customer_type']);
        $batch = isset($params['batch_id']) ? json_decode($params['batch_id'], true) : [];
        if (is_array($batch) && count($batch)) {
            $query->whereIn("crm_customer.id", function($query) use ($batch, $params) {
                $query->select("customer_id");
                if ($params['customer_type'] == Customer::TYPE_MARKETING) {
                    $query->from("crm_marketing_list");
                } else {
                    $query->from("crm_white_list");
                }
                $query->whereIn('batch_id', $batch);
                $query->where('indate', '>', date("Y-m-d H:i:s"));
                $query->where('status', "1");
            });
        }
        #最大逾期天数
        if (isset($params['max_overdue_days']) && !empty($params['max_overdue_days'])) {
            $query->where("crm_customer_status.max_overdue_days", "<=", $params['max_overdue_days']);
        }
        $lastLogin = isset($params['last_login']) ? json_decode($params['last_login'], true) : [];
        if (is_array($lastLogin) && count($lastLogin) && is_numeric($lastLogin['start']) && is_numeric($lastLogin['end'])) {
            $query->whereBetween(DB::raw("TIMESTAMPDIFF(DAY,crm_customer_status.last_login_time,NOW())"), [$lastLogin['start'], $lastLogin['end']]);
        }
        #手机号状态
        if (isset($params['telephone_status']) && !empty($params['telephone_status'])) {
            $query->where("telephone_status", $params['telephone_status']);
        }
        # 过滤掉 状态停留时间不达标
        if (isset($params['phone_status_stop_days']) && !empty($params['phone_status_stop_days'])) {
            $rang = [$params['phone_status_stop_days']];
            if (isset($params['phone_status_stop_days_2']) && !empty($params['phone_status_stop_days_2'])) {
                $rang = range($params['phone_status_stop_days'], $params['phone_status_stop_days_2']);
            }
            $query->whereIn(DB::raw("datediff(NOW(),crm_customer_status.status_updated_time)"), $rang);
        }
        $listCount = $query->count();
        return $listCount;
    }

    public function getTaskCustomer(CrmMarketingTask $task, $all = true) {
        MerchantHelper::helper()->setMerchantId($task->merchant_id);
        $query = Customer::model()->newQuery();
        $query->join("crm_customer_status", function($join) {
            $join->on("crm_customer_status.customer_id", "=", "crm_customer.id");
            $join->where("crm_customer_status.merchant_id", '=', MerchantHelper::getMerchantId());
        });
        $query->select(\DB::raw("`crm_customer_status`.clm_level,"
                        . "`crm_customer_status`.type,"
                        . "`crm_customer_status`.status,"
                        . "`crm_customer_status`.batch_id,"
                        . "`crm_customer_status`.suggest_time,"
                        . "`crm_customer_status`.last_login_time,"
                        . "`crm_customer_status`.max_overdue_days,"
                        . "`crm_customer_status`.status_stop_days,"
                        . "`crm_customer_status`.settle_times,"
                        . "`crm_customer_status`.last_settle_time,"
                        . "`crm_customer_status`.status_updated_time,"
                        . "crm_customer_status.remark,"
                        . "crm_customer_status.merchant_id,"
                        . "crm_customer.id,"
                        . "crm_customer.created_at,"
                        . "crm_customer.telephone,"
                        . "crm_customer.telephone_status,"
                        . "crm_customer.telephone_check_time,"
                        . "crm_customer.email,"
                        . "crm_customer.fullname,"
                        . "crm_customer.birthday,"
                        . "crm_customer.id_type,"
                        . "crm_customer.id_number,"
                        . "crm_customer.gender"));
        #处理等级
        $clmLevel = $task->clm_level ? json_decode($task->clm_level, true) : [];
        if (is_array($clmLevel) && $clmLevel) {
            $query->whereIn("crm_customer_status.clm_level", $clmLevel);
        }
        #处理用户状态
        $customerStatus = $task->customer_status ? json_decode($task->customer_status, true) : [];
        if (is_array($customerStatus) && $customerStatus) {
            $query->whereIn("crm_customer_status.status", $customerStatus);
        }
        #用户群体
        $query->where("crm_customer_status.type", $task->customer_type);
        $batch = json_decode($task->batch_id, true);
        if (is_array($batch) && $batch) {
            $query->whereIn("crm_customer.id", function($query) use ($batch, $task) {
                $query->select("customer_id");
                if ($task->customer_type == Customer::TYPE_MARKETING) {
                    $query->from("crm_marketing_list");
                } else {
                    $query->from("crm_white_list");
                }
                $query->whereIn('batch_id', $batch);
                $query->where('indate', '>', date("Y-m-d H:i:s"));
                $query->where('status', "1");
            });
        }
        #最大逾期天数
        if ($task->max_overdue_days) {
            $query->where("crm_customer_status.max_overdue_days", "<=", $task->max_overdue_days);
        }
        $lastLogin = $task->last_login ? json_decode($task->last_login, true) : "";
        if (is_array($lastLogin) && is_numeric($lastLogin['start']) && is_numeric($lastLogin['end'])) {
            $query->whereBetween(DB::raw("TIMESTAMPDIFF(DAY,crm_customer_status.last_login_time,NOW())"), [$lastLogin['start'], $lastLogin['end']]);
        }
        #手机号状态
        if ($task->telephone_status) {
            $query->where("telephone_status", $task->telephone_status);
        }
        if ($task->phone_status_stop_days) {
            $rang = [$task->phone_status_stop_days];
            if ($task->phone_status_stop_days_2) {
                $rang = range($task->phone_status_stop_days, $task->phone_status_stop_days_2);
            }
            $query->whereIn(DB::raw("datediff(NOW(),crm_customer_status.status_updated_time)"), $rang);
        }
        # 排除已分配的
        if ($all == false) {
            $query->whereNotIn('crm_customer.id', function($query) use ($task) {
                $query->select('customer_id');
                $query->from('crm_marketing_phone_assign')->where("task_id", $task->id);
            });
        }
        $list = $query->get();

        if (\Yunhan\Utils\Env::isDevOrTest() && app()->runningInConsole()) {
            echo $task->task_name . "获取Customer" . PHP_EOL;
            echo $query->toSql() . PHP_EOL;
        }
        return $list;
    }

    public function taskStatus($taskId) {
        $task = CrmMarketingTask::model()->getOne($taskId);
        if ($task) {
            if ($task->status == CrmMarketingTask::STATUS_NORMAL) {
                $task->status = CrmMarketingTask::STATUS_FORGET;
            } else {
                $task->status = CrmMarketingTask::STATUS_NORMAL;
            }
            return $task->save();
        }
        return $this->outputException("任务不存在");
    }

}
