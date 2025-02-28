<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Crm;

use Admin\Services\BaseService;
use Common\Models\Crm\MarketingPhoneAssign;
use Common\Models\Crm\MarketingPhoneAssignLog;
use Common\Models\Crm\MarketingPhoneJob;
use Common\Models\Crm\MarketingPhoneLog;
use Common\Models\Crm\CrmMarketingTask;
use Common\Models\Crm\Customer;
use Admin\Models\Staff\Staff;
use Illuminate\Support\Facades\DB;
use Common\Jobs\Crm\TelemarketingJob;
use Common\Services\NewClm\ClmCustomerServer;
use Common\Models\Crm\CustomerTelephone;
use Admin\Exports\Crm\TelemarketingExport;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;

class TelemarketingServer extends BaseService {

    public function getSalesList($adminId, $params) {
        $mechantId = MerchantHelper::getMerchantId();
        $where = " assign.status=" . MarketingPhoneAssign::STATUS_NORMAL. " AND assign.merchant_id='{$mechantId}' " ;
        $type = 0;
        if (isset($params['type']) && $params['type']) {
            $type = $params['type'];
        }
        if ($type == '1') {
            $where .= " AND assign.last_call_time is null ". " AND saler_id='{$adminId}' ";
        } elseif ($type == '2') {
            $where .= " AND assign.last_call_time is not null ". " AND saler_id='{$adminId}' ";
        }elseif (isset($params['saler_id']) && $params['saler_id']){
            $where .= " AND saler_id='{$adminId}' ";
        }
        $pageSize = 10;
        $currentPage = 1;
        if (isset($params['taskName']) && $params['taskName']) {
            $where .= " AND task.task_name like '%{$params['taskName']}%'";
        }
        if (isset($params['task_id']) && $params['task_id']) {
            $where .= " AND task.id='{$params['task_id']}'";
        }
        if (isset($params['telephone']) && $params['telephone']) {
            $where .= " AND customer.telephone='{$params['telephone']}'";
        }
        if (isset($params['status']) && $params['status']) {
            $where .= " AND cs.status='{$params['status']}'";
        }
        if (isset($params['customer_type']) && $params['customer_type']) {
            $where .= " AND cs.type='{$params['customer_type']}'";
        }
        if (isset($params['start_date']) && $params['start_date']) {
            $where .= " AND assign.assign_time>='{$params['start_date']}'";
        }
        if (isset($params['end_date']) && $params['end_date']) {
            $where .= " AND assign.assign_time<='{$params['end_date']}'";
        }
        if (isset($params['page_size']) && $params['page_size']) {
            $pageSize = $params['page_size'];
        }
        if (isset($params['per_page']) && $params['per_page']) {
            $pageSize = $params['per_page'];
        }
        if (isset($params['page']) && $params['page']) {
            $currentPage = $params['page'];
        }
        $sel = "select assign.*,customer.telephone,cs.type as customer_type,cs.suggest_time,customer.fullname,customer.birthday,customer.gender,cs.status as customer_status,task.task_name ";
        //跟进次数查询
        if (isset($params['called_times']) && $params['called_times']) {
            $sel .= ",count(assign.id) as ids" ;
        }
        $from = " from crm_marketing_phone_assign assign
INNER JOIN crm_customer customer ON assign.customer_id=customer.id
INNER JOIN crm_customer_status cs ON cs.customer_id=customer.id AND cs.merchant_id='".MerchantHelper::helper()->getMerchantId()."'
INNER JOIN crm_marketing_task task ON task.id=assign.task_id";
        $sql = $sel.$from;
        //跟进次数查询
        if (isset($params['called_times']) && $params['called_times']) {
            $sql .= " INNER JOIN crm_marketing_phone_log log ON assign.id=log.assign_id AND assign.customer_id=log.customer_id
                 AND assign.saler_id=log.operator_id AND assign.task_id=log.task_id";
        }
        //最后一次跟进结果
        if (isset($params['last_sales_result']) && $params['last_sales_result']) {
            //没有跟进次数查询
            if (!str_contains($sql," INNER JOIN crm_marketing_phone_log log ON assign.id=log.assign_id AND assign.customer_id=log.customer_id
                 AND assign.saler_id=log.operator_id AND assign.task_id=log.task_id")){
                $sql .= " INNER JOIN crm_marketing_phone_log log ON assign.id=log.assign_id AND assign.customer_id=log.customer_id
                 AND assign.saler_id=log.operator_id AND assign.task_id=log.task_id AND log.call_result={$params['last_sales_result']}";
            }else{
                //有跟进次数查询
                $sql .= " AND log.call_result={$params['last_sales_result']}";
            }
            $where .= " AND assign.id=(select assign_id from crm_marketing_phone_log where assign_id=assign.id order by id desc limit 1)";
        }
        //跟进次数查询
        if (isset($params['called_times']) && $params['called_times']) {
            $where .= " GROUP BY assign.id HAVING ids=".$params['called_times'];
        }
        $sql .= " WHERE {$where}
ORDER BY assign.id DESC";
        $limit = " LIMIT " . (($currentPage - 1) * $pageSize) . ", {$pageSize};";
//        DB::connection()->enableQueryLog();
        $res = DB::select($sql);
//        dd(DB::getQueryLog());

        $data = [];
        $data['total'] = count($res);
        $data['list'] = DB::select($sql . $limit);
        foreach ($data['list'] as $item) {
            $lastLog = MarketingPhoneLog::model()->query()->where("assign_id", $item->id)->orderByDesc("id")->first();
            $item->saler_name = null;
            $item->status_text = $item->last_call_time ? '电销中':'待电销';
            if ($item->saler_id){
                $staff = Staff::model()->getOne($item->saler_id);
                $item->saler_name = $staff ? $staff->username.'_'.$staff->nickname:null;
            }
            $item->customer_type_txt = isset($item->customer_type) ? t(Customer::TYPE_LIST[$item->customer_type], "crm") : '';
            $item->call_status_txt = isset($lastLog->call_status) && isset(MarketingPhoneLog::CALL_STATUS[$lastLog->call_status]) ? t(MarketingPhoneLog::CALL_STATUS[$lastLog->call_status], "crm") : "";
            $item->call_result_txt = isset($lastLog->call_result) && isset(MarketingPhoneLog::CALL_RESULT[$lastLog->call_result]) ? t(MarketingPhoneLog::CALL_RESULT[$lastLog->call_result], "crm") : "";
            $item->call_refusal_txt = isset($lastLog->call_refusal) && isset(MarketingPhoneLog::CALL_REFUSAL[$lastLog->call_refusal]) ? t(MarketingPhoneLog::CALL_REFUSAL[$lastLog->call_refusal], "crm") : "";
            $item->customer_status_txt = isset($item->customer_status) ? t(Customer::STATUS_CUSTOMER[$item->customer_status], "crm") : "";
            $item->history = $this->getHistory($item->customer_id,$item->task_id,$item->saler_id);

            $item->today_marketing_count = MarketingPhoneLog::model()->newQuery()
                ->where("customer_id", $item->customer_id)
                ->where("operator_id", $item->saler_id)
                ->where("task_id", $item->task_id)
//                ->where("created_at", ">", date("Y-m-d"))
                ->count();

            $item->clm_limit = 0;
            $item->clm_open_rate = 0;
            $customer = Customer::model()->getOne($item->customer_id);
            if (isset($customer->user)) {
                try {
                    $clmCustomer = ClmCustomerServer::server()->getCustomer($customer->user);
                } catch (\Exception $e) {
                    //echo "CLM" . $e->getMessage() . PHP_EOL;
                }
            }
            if (isset($clmCustomer)) {
                $item->clm_limit = $clmCustomer->calcAvailableAmount();
                $item->clm_open_rate = $clmCustomer->getCurrentLevelAmount()->clm_interest_discount;
            }
            $item->coupon_interest_free_count = CustomerServer::server()->getAvailableCouponCount($customer, 1);
            $item->coupon_voucher_count = CustomerServer::server()->getAvailableCouponCount($customer, 2);
            $item->coupon_voucher_limit = CustomerServer::server()->getDeductibleCouponAmount($customer);
            $item->other_contact = CustomerTelephone::model()->newQuery()->where('customer_id', $item->customer_id)->groupBy("telephone")->get()->pluck('telephone');
        }
        $data['page_total'] = ceil($data['total'] / $pageSize);
        $data['current_page'] = $currentPage;
        $data['page_size'] = $pageSize;
        return $data;
    }

    public function getHistory($customerId,$taskId=null,$salerId=null) {
        $sql = "select log.*,staff.nickname from crm_marketing_phone_log log INNER JOIN staff ON staff.id=log.operator_id WHERE customer_id='{$customerId}' AND task_id='{$taskId}' AND operator_id='{$salerId}' AND staff.merchant_id='".MerchantHelper::helper()->getMerchantId()."' ORDER BY id DESC ";
        $res = DB::select($sql);
        $data = [];
        foreach ($res as $item) {
            $data[] = "{$item->created_at}/{$item->nickname}/" .
                (MarketingPhoneLog::CALL_STATUS[$item->call_status] ?? "") . "/" .
                (MarketingPhoneLog::CALL_RESULT[$item->call_result] ?? "") . "/" .
                $item->remark;
//                (MarketingPhoneLog::CALL_REFUSAL[$item->call_refusal] ?? "");
        }
        return $data;
    }

    public function finishTbl($params) {
        $where = " status<>1 ";
        $pageSize = array_get($params, 'page_size', 10);
        $currentPage = array_get($params, 'page', 1);
        if (isset($params['saler_id']) && $params['saler_id']) {
            $where .= " AND saler_id='" . $params['saler_id'] . "'";
        }elseif (!LoginHelper::isSuper()) {
            $where .= " AND saler_id='" . LoginHelper::getAdminId() . "'";
        }
        if (isset($params['telephone']) && $params['telephone']) {
            $where .= " AND telephone='{$params['telephone']}'";
        }
        if (isset($params['customer_status']) && $params['customer_status']) {
            $where .= " AND customer_status='{$params['customer_status']}'";
        }
        if (isset($params['start_date']) && $params['start_date']) {
            $where .= " AND assign_time>='{$params['start_date']}'";
        }
        if (isset($params['end_date']) && $params['end_date']) {
            $where .= " AND assign_time<='" . $params['end_date'] . " 23:59:59" . "'";
        }
        if (isset($params['call_status']) && $params['call_status']) {
            $where .= " AND call_status='" . $params['call_status'] . "'";
        }
        if (isset($params['call_result']) && $params['call_result']) {
            $where .= " AND call_result='" . $params['call_result'] . "'";
        }
        if (isset($params['call_refusal']) && $params['call_refusal']) {
            $where .= " AND call_refusal='" . $params['call_refusal'] . "'";
        }
        $sql = "select * from (SELECT assign.*,
log.id as log_id, 
log.call_status,
log.call_result,
log.call_refusal,
cs.type as customer_type,
cs.`status` as customer_status,cutr.telephone,
staff.nickname as operator_nickname,
log.created_at operation_time,
cs.remark as list_remark,
log.remark as log_remark,
task.task_name 
FROM crm_marketing_phone_assign assign
INNER JOIN crm_customer cutr ON assign.customer_id=cutr.id
INNER JOIN crm_marketing_task task ON assign.task_id=task.id
INNER JOIN crm_customer_status cs ON cs.customer_id=cutr.id AND cs.merchant_id='".MerchantHelper::helper()->getMerchantId()."'
INNER JOIN staff ON staff.id=assign.saler_id AND staff.merchant_id='".MerchantHelper::helper()->getMerchantId()."'
LEFT JOIN crm_marketing_phone_log log ON assign.id=log.assign_id AND log.id = (select id from crm_marketing_phone_log WHERE assign_id=assign.id order by id desc limit 1)
) a
WHERE $where
ORDER BY id DESC";
        if (isset($params['page_size']) && $params['page_size']) {
            $pageSize = $params['page_size'];
        }
        if (isset($params['page']) && $params['page']) {
            $currentPage = $params['page'];
        }
        $limit = " LIMIT " . (($currentPage - 1) * $pageSize) . ", {$pageSize};";
        $res = DB::select($sql);
        $data = [];
        $data['total'] = count($res);
        $data['data'] = DB::select($sql . $limit);
        foreach ($data['data'] as $item) {
            $customer = Customer::model()->getOne($item->customer_id);
            $item->customer_type_txt = isset($item->customer_type) ? t(Customer::TYPE_LIST[$item->customer_type], "crm") : '';
            $item->call_status_txt = isset($item->call_status) && isset(MarketingPhoneLog::CALL_STATUS[$item->call_status]) ? t(MarketingPhoneLog::CALL_STATUS[$item->call_status], "crm") : "";
            $item->call_result_txt = isset($item->call_result) && isset(MarketingPhoneLog::CALL_RESULT[$item->call_result]) ? t(MarketingPhoneLog::CALL_RESULT[$item->call_result], "crm") : "";
            $item->call_refusal_txt = isset($item->call_refusal) && isset(MarketingPhoneLog::CALL_RESULT[$item->call_refusal]) ? t(MarketingPhoneLog::CALL_REFUSAL[$item->call_refusal], "crm") : "";
            $item->customer_status_txt = isset($item->customer_status) ? t(Customer::STATUS_CUSTOMER[$item->customer_status], "crm") : "";
            $item->clm_limit = 0;
            $item->history = $this->getHistory($item->customer_id,$item->task_id,$item->saler_id);
            if (isset($customer->user)) {
                try {
                    $clmCustomer = ClmCustomerServer::server()->getCustomer($customer->user);
                } catch (\Exception $e) {
                    //echo "CLM" . $e->getMessage() . PHP_EOL;
                }
                if (isset($clmCustomer)) {
                    $item->clm_limit = $clmCustomer->calcAvailableAmount();
                }
            }
            $item->customer = ["telephone" => $item->telephone];
        }
        $data['page_total'] = ceil($data['total'] / $pageSize);
        $data['current_page'] = $currentPage;
        $data['per_page'] = $pageSize;
        return $data;
    }

    public function telemarketingRecord($params) {
        $where = " 1=1 ";
        $pageSize = array_get($params, 'page_size', 10);
        $currentPage = array_get($params, 'page', 1);
        if (isset($params['saler_id']) && $params['saler_id']) {
            $where .= " AND saler_id='" . $params['saler_id'] . "'";
        }elseif (!LoginHelper::isSuper()) {
//            $where .= " AND saler_id='" . LoginHelper::getAdminId() . "'";
        }
        if (isset($params['telephone']) && $params['telephone']) {
            $where .= " AND telephone='{$params['telephone']}'";
        }
        if (isset($params['customer_status']) && $params['customer_status']) {
            $where .= " AND customer_status='{$params['customer_status']}'";
        }
        if (isset($params['start_date']) && $params['start_date']) {
            $where .= " AND assign_time>='{$params['start_date']}'";
        }
        if (isset($params['end_date']) && $params['end_date']) {
            $where .= " AND assign_time<='" . $params['end_date'] . " 23:59:59" . "'";
        }
        if (isset($params['call_status']) && $params['call_status']) {
            $where .= " AND call_status='" . $params['call_status'] . "'";
        }
        if (isset($params['call_result']) && $params['call_result']) {
            $where .= " AND call_result='" . $params['call_result'] . "'";
        }
        if (isset($params['call_refusal']) && $params['call_refusal']) {
            $where .= " AND call_refusal='" . $params['call_refusal'] . "'";
        }
        if (isset($params['task_name']) && $params['task_name']) {
            $where .= " AND task_name='" . $params['task_name'] . "'";
        }
        $sql = "select * from (SELECT assign.*,
log.id as log_id, 
log.call_status,
log.call_result,
log.call_refusal,
cs.type as customer_type,
cs.`status` as customer_status,cutr.telephone,
staff.nickname as operator_nickname,
log.created_at operation_time,
cs.remark as list_remark,
log.remark as log_remark,
task.task_name 
FROM crm_marketing_phone_assign assign
INNER JOIN crm_customer cutr ON assign.customer_id=cutr.id
INNER JOIN crm_marketing_task task ON assign.task_id=task.id
INNER JOIN crm_customer_status cs ON cs.customer_id=cutr.id AND cs.merchant_id='".MerchantHelper::helper()->getMerchantId()."'
INNER JOIN staff ON staff.id=assign.saler_id AND staff.merchant_id='".MerchantHelper::helper()->getMerchantId()."'
LEFT JOIN crm_marketing_phone_log log ON assign.id=log.assign_id AND log.id = (select id from crm_marketing_phone_log WHERE assign_id=assign.id order by id desc limit 1)
) a
WHERE $where
ORDER BY last_call_time DESC,id DESC";
        if (isset($params['page_size']) && $params['page_size']) {
            $pageSize = $params['page_size'];
        }
        if (isset($params['page']) && $params['page']) {
            $currentPage = $params['page'];
        }
        $limit = " LIMIT " . (($currentPage - 1) * $pageSize) . ", {$pageSize};";
        $res = DB::select($sql);
        $data = [];
        $data['total'] = count($res);
        $data['data'] = DB::select($sql . $limit);
        foreach ($data['data'] as $item) {
            $customer = Customer::model()->getOne($item->customer_id);
            $item->customer_type_txt = isset($item->customer_type) ? t(Customer::TYPE_LIST[$item->customer_type], "crm") : '';
            $item->call_status_txt = isset($item->call_status) && isset(MarketingPhoneLog::CALL_STATUS[$item->call_status]) ? t(MarketingPhoneLog::CALL_STATUS[$item->call_status], "crm") : "";
            $item->call_result_txt = isset($item->call_result) && isset(MarketingPhoneLog::CALL_RESULT[$item->call_result]) ? t(MarketingPhoneLog::CALL_RESULT[$item->call_result], "crm") : "";
            $item->call_refusal_txt = isset($item->call_refusal) && isset(MarketingPhoneLog::CALL_RESULT[$item->call_refusal]) ? t(MarketingPhoneLog::CALL_REFUSAL[$item->call_refusal], "crm") : "";
            $item->customer_status_txt = isset($item->customer_status) ? t(Customer::STATUS_CUSTOMER[$item->customer_status], "crm") : "";
            $item->clm_limit = 0;
            $item->history = $this->getHistory($item->customer_id,$item->task_id,$item->saler_id);
            if (isset($customer->user)) {
                try {
                    $clmCustomer = ClmCustomerServer::server()->getCustomer($customer->user);
                } catch (\Exception $e) {
                    //echo "CLM" . $e->getMessage() . PHP_EOL;
                }
                if (isset($clmCustomer)) {
                    $item->clm_limit = $clmCustomer->calcAvailableAmount();
                }
            }
            $item->customer = ["telephone" => $item->telephone];
        }
        $data['page_total'] = ceil($data['total'] / $pageSize);
        $data['current_page'] = $currentPage;
        $data['per_page'] = $pageSize;
        return $data;
    }

    public function finishList($params) {
        $query = MarketingPhoneAssign::model()->newQuery()->where("status", "<>", 1);
        if (isset($params['telephone']) && $params['telephone']) {
            $telephone = $params['telephone'];
            $query->whereHas("customer", function($customer) use ($telephone) {
                $customer->where("telephone", $telephone);
            });
        }
        if (isset($params['customer_status']) && $params['customer_status']) {
            $customerStatus = $params['customer_status'];
            $query->whereHas("customer", function($customer) use ($customerStatus) {
                $customer->where("status", $customerStatus);
            });
        }
        if (isset($params['start_date']) && $params['start_date']) {
            $query->where("assign_time", ">=", $params['start_date']);
        }
        if (isset($params['end_date']) && $params['end_date']) {
            $query->where("assign_time", "<", $params['end_date'] . " 23:59:59");
        }
        if (isset($params['call_status']) && $params['call_status']) {
            $query->whereHas("log", function($log) use ($params) {
                $log->where("call_status", $params['call_status']);
            });
        }
        if (isset($params['call_result']) && $params['call_result']) {
            $query->whereHas("log", function($log) use ($params) {
                $log->where("call_result", $params['call_result']);
            });
        }
        if (isset($params['call_refusal']) && $params['call_refusal']) {
            $query->whereHas("log", function($log) use ($params) {
                $log->where("call_refusal", $params['call_refusal']);
            });
        }

        $query->orderByDesc("id");
        $res = $query->paginate($params['page_size'] ?? 10);
        foreach ($res as $item) {
            $customer = Customer::model()->getOne($item->customer_id);
            $item->telephone = $customer->telephone;
            $item->operator_nickname = $item->saler->nickname;
            $item->customer_type_txt = isset($item->customer->type) ? t(Customer::TYPE_LIST[$item->customer->type], "crm") : '';
            $item->call_status_txt = isset($item->log->call_status) && isset(MarketingPhoneLog::CALL_STATUS[$item->log->call_status]) ? t(MarketingPhoneLog::CALL_STATUS[$item->log->call_status], "crm") : "";
            $item->call_result_txt = isset($item->log->call_result) && isset(MarketingPhoneLog::CALL_RESULT[$item->log->call_result]) ? t(MarketingPhoneLog::CALL_RESULT[$item->log->call_result], "crm") : "";
            $item->call_refusal_txt = isset($item->log->call_refusal) && isset(MarketingPhoneLog::CALL_RESULT[$item->log->call_refusal]) ? t(MarketingPhoneLog::CALL_REFUSAL[$item->log->call_refusal], "crm") : "";
            $item->customer_status_txt = isset($item->customerStatus()->status) ? t(Customer::STATUS_CUSTOMER[$item->customerStatus()->status], "crm") : "";
            $item->operation_time = $item->log->created_at->toDateTimeString();
            $item->list_remark = $customer->remark;
            $item->clm_limit = 0;
            $item->log_remark = $item->log->remark;
            if (isset($customer->user)) {
                try {
                    $clmCustomer = ClmCustomerServer::server()->getCustomer($customer->user);
                } catch (\Exception $e) {
                    //echo "CLM" . $e->getMessage() . PHP_EOL;
                }
            }
            if (isset($clmCustomer)) {
                $item->clm_limit = $clmCustomer->calcAvailableAmount();
            }
        }
        return $res;
    }

    public function addLog($params) {
        $assign = MarketingPhoneAssign::model()->getOne($params['assign_id']);
        $params['customer_id'] = $assign->customer_id;
        $params['task_id'] = $assign->task_id;
        $params['merchant_id'] = MerchantHelper::helper()->getMerchantId();
        $assign->last_call_time = date("Y-m-d H:i:s");
        $customer = Customer::model()->getOne($assign->customer_id);
        if ($customer) {
            if (isset($params['token'])) {
                unset($params['token']);
            }
            $params['customer_status'] = $customer->status;
            $params['operator_id'] = \Common\Utils\LoginHelper::getAdminId();

//            if ($params['call_status'] == MarketingPhoneLog::CALL_STATUS_INVALID_NUMBER && $params['call_result'] == MarketingPhoneLog::CALL_RESULT_QUIT_MARKETING) {
            if ($params['call_result'] == MarketingPhoneLog::CALL_RESULT_INVALID_NUMBER ) {
                $assign->status = MarketingPhoneAssign::STATUS_FORGET;
                $assign->stop_reason = "invalid number && quit marketing";
            }
//            if ($params['call_status'] != MarketingPhoneLog::CALL_STATUS_INVALID_NUMBER && $params['call_result'] == MarketingPhoneLog::CALL_RESULT_QUIT_MARKETING) {
            if ( $params['call_result'] == MarketingPhoneLog::CALL_RESULT_QUIT_MARKETING) {
                $assign->status = MarketingPhoneAssign::STATUS_FORGET;
                $assign->stop_reason = "quit marketing";
            }
            if ($params['call_result'] == MarketingPhoneLog::CALL_RESULT_NO_INTENTION_TO_APPLY || $params['call_result'] == MarketingPhoneLog::CALL_RESULT_OTHER_PERSON_USE_PHONE_AND_HAVE_NO_INTENTION_TO_APPLY) {
                $assign->status = MarketingPhoneAssign::STATUS_FORGET;
                $assign->stop_reason = "No intention to apply && other person use phone and have no intention to apply";
            }
            $resSave = MarketingPhoneLog::model()->createModel($params);
            if ($params['call_status'] == MarketingPhoneLog::CALL_STATUS_NO_ANSWER) {
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
	AND call_status = 1 
	AND task_id=clog.task_id
	AND id < clog.id 
ORDER BY
	id DESC 
	LIMIT 1 
	) AS diffhour 
FROM
	crm_marketing_phone_log clog 
WHERE
	call_status = 1 
	AND customer_id = {$customer->id} 
	AND task_id={$assign->task_id} 
	) a 
WHERE
	diffhour >=1";
                $res = \DB::select($sql);
                if ($res[0]->c >= 3) {
                    $assign->status = MarketingPhoneAssign::STATUS_FORGET;
                    $assign->stop_reason = "NO ANSWER 3 Times";
                }
            }

            if ($resSave) {
                $assign->save();
            }
        }
        return false;
    }

    public function assign($params) {
        if (isset($params['token'])) {
            unset($params['token']);
        }
        $params['task_id'] = isset($params['id']) ? $params['id'] : $params['task_id'];
        unset($params['id']);
        $params['saler_ids'] = is_array($params['saler_ids']) ? json_encode($params['saler_ids']) : $params['saler_ids'];
        $params['admin_id'] = \Common\Utils\LoginHelper::getAdminId();
        $params['merchant_id'] = MerchantHelper::helper()->getMerchantId();
        $res = MarketingPhoneJob::model()->createModel($params);
        if ($res) {
            dispatch(new TelemarketingJob($res->id));
        }
        return $res;
    }

    public function cancelAssign($params) {
        $params['ids'] = is_array($params['ids']) ? $params['ids'] : json_encode($params['ids']);
        $cancels = MarketingPhoneAssign::model()->whereIn('id',$params['ids'])->get();
        $merchantId = MerchantHelper::helper()->getMerchantId();
        $adminId = \Common\Utils\LoginHelper::getAdminId();
        foreach ($cancels as $cancel){
            DB::beginTransaction();
            try {
                $paramsLog = [];
                $paramsLog['merchant_id'] = $merchantId;
                $paramsLog['assign_id'] = $cancel->id;
                $paramsLog['customer_id'] = $cancel->customer_id;
                $paramsLog['task_id'] = $cancel->task_id;
                //删除的分配为-1
                $paramsLog['saler_id'] = -1;
                $paramsLog['from_saler_id'] = $cancel->saler_id;
                $paramsLog['assign_time'] = date('Y-m-d H:i:s');
                $paramsLog['status'] = $cancel->status;
                $paramsLog['admin_id'] = $adminId;
                if (MarketingPhoneAssignLog::model()->createModel($paramsLog)){
                    $cancel->delete();
                }
                DB::commit();
            }catch (\Exception $exception){
                DB::rollBack();
                return $this->outputError($exception->getMessage());
            }
        }
        return $this->outputSuccess('cancel assign success1');
    }

    public function report($params) {
        $where = " 1 ";
        if (isset($params['start_date']) && $params['start_date']) {
            $where .= " AND report_date>='{$params['start_date']}'";
        }
        if (isset($params['end_date']) && $params['end_date']) {
            $where .= " AND report_date<='{$params['end_date']}'";
        }
        if (isset($params['saler_id']) && $params['saler_id']) {
            $where .= " AND saler_id='{$params['saler_id']}'";
        }
        if (isset($params['task_name']) && $params['task_name']) {
            $where .= " AND task_id='{$params['task_name']}'";
        }
        $dateName = "";
        switch ($params['date_type']) {
            case "year":
                $dateName = "left(report_date,4)";
                break;
            case "month":
                $dateName = "concat(left(report_date,4),'-',Left(MONTHNAME(rpt.report_date),3))";
                break;
            case "week":
                $dateName = "concat(left(report_date,4),'#',date_format(report_date,'%U'))";
                break;
            default:
                $dateName = "report_date";
        }
        $sql = "SELECT 
rpt.saler_id,
stf.username as saler_name,
{$dateName} as areport_date,
SUM(total) as total,
SUM(agree_number) as agree_number,
SUM(call_reject_number) as call_reject_number,
SUM(neutral_number) as neutral_number,
SUM(miss_call_number) as miss_call_number,
SUM(wrong_number) as wrong_number,
SUM(apply_number) as apply_number,
SUM(pass_number) as pass_number,
SUM(reject_number) as reject_number
FROM crm_marketing_phone_report rpt
INNER JOIN staff stf ON rpt.saler_id=stf.id AND stf.merchant_id='".MerchantHelper::helper()->getMerchantId()."'
WHERE {$where}
GROUP BY saler_id,areport_date
ORDER BY areport_date DESC,total DESC";
        $res = DB::select($sql);
        foreach ($res as $item) {
            $item->report_date = $item->areport_date;
            $item->agree_number_rate = round($item->agree_number / $item->total * 100, 2) . "%";
            $item->call_reject_rate = round($item->call_reject_number / $item->total * 100, 2) . "%";
            $item->miss_call_rate = round($item->miss_call_number / $item->total * 100, 2) . "%";
            $item->wrong_number_rate = round($item->wrong_number / $item->total * 100, 2) . "%";
            $item->apply_number_rate = round($item->apply_number / $item->total * 100, 2) . "%";
//            $item->pass_number_rate = round($item->pass_number / $item->total * 100, 2) . "%";
//            $item->conv_rate = $item->apply_number == 0 ? 0 : round(($item->total - $item->reject_number) / $item->total * 100, 2) . "%";
            $item->conv_rate = $item->pass_number ? round($item->pass_number / $item->total * 100, 2) . "%" : "--";
            $item->pass_number_rate = $item->pass_number ? round($item->pass_number / $item->apply_number * 100, 2) . "%" : "--";
        }
        if ($this->getExport()) {
            TelemarketingExport::getInstance()->csvArray($res, TelemarketingExport::SCENE_EXPORT);
        }
        return $res;
    }

    public function taskList($params) {
        $size = array_get($params, 'size', 10);
        $query = CrmMarketingTask::model()->newQuery()->where("merchant_id", MerchantHelper::helper()->getMerchantId());
        $query->where("task_type", CrmMarketingTask::TYPE_PHONE);
        if (isset($params['task_name'])) {
            $query->where("task_name", 'like', "%{$params['task_name']}%");
        }
        if (isset($params['task_status'])) {
            $query->where("status", $params['task_status']);
        }
        if (isset($params['task_type'])) {
            $query->where("task_type", $params['task_type']);
        }
        if (isset($params['admin_id'])) {
            $query->where("admin_id", $params['admin_id']);
        }
        $query->orderByDesc("id");
        $res = $query->paginate($size);
        foreach ($res as $item) {
            //$item->phone_total = count(MarketingServer::server()->getTaskCustomer($item));
            $item->status_txt = isset(CrmMarketingTask::STATUS[$item->status]) ? t(CrmMarketingTask::STATUS[$item->status], "crm") : '';
            $item->wait_assign_total = $item->phone_total - $item->phone_assign_total > 0 ? $item->phone_total - $item->phone_assign_total : 0;
            $item->apply_rate = $item->apply_total && $item->phone_total ? round($item->apply_total / $item->phone_total * 100, 2) . "%" : "-";
            $item->paid_rate = $item->paid_total && $item->phone_total ? round($item->paid_total / $item->phone_total * 100, 2) . "%" : "-";
            $item->admin_name = $item->admin->nickname;
        }
        return $res;
    }

    public function getCustomerCount($params) {
        if (isset($params['task_id'])) {
            $task = CrmMarketingTask::model()->getOne($params['task_id']);
            $phoneTotal = $task->phone_total;
            return $phoneTotal - $task->phone_assign_total > 0 ? $phoneTotal - $task->phone_assign_total : 0;
        } else {
            $this->outputError("参数错误");
        }
    }

}
