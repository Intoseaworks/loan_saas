<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Collection;

use Admin\Models\Staff\Staff;
use Approve\Admin\Services\Approval\ApproveService;
use Common\Models\Approve\ApproveUserPool;
use Common\Models\Collection\Collection;
use Common\Models\Collection\CollectionOnlineLog;
use Common\Models\Collection\CollectionRecord;
use Common\Models\Crm\MarketingPhoneAssign;
use Common\Services\BaseService;
use Common\Services\Collection\CollectionServer;
use Common\Services\Crm\CustomerServer;
use Common\Utils\Data\DateHelper;
use Common\Utils\Host\HostHelper;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use DB;

class CollectionOnlineServer extends BaseService {

    public function online() {
        $adminId = LoginHelper::getAdminId();
        $lastLog = CollectionOnlineLog::model()->newQuery()->where("admin_id", $adminId)->orderByDesc('id')->first();
        $insert = [
            "status" => CollectionOnlineLog::STATUS_ONLINE,
            "admin_id" => $adminId,
            "created_at" => DateHelper::dateTime(),
            "ip" => HostHelper::getIp(),
        ];
        if ($lastLog) {
            if (CollectionOnlineLog::STATUS_ONLINE == $lastLog->status) {
                $lastLog = $this->offline(['status_value' => 'system out']);
            }
            DB::beginTransaction();
            if (date("Y-m-d") == substr($lastLog->created_at, 0, 10)) {
                # 计算休息实时间
                $useTime = time() - strtotime($lastLog->created_at);
                $lastLog->use_time = $useTime > 0 ? $useTime : 1;
            } else {
                $lastLog->use_time = 1;
            }
            $lastLog->save();
            DB::commit();
        }
        return CollectionOnlineLog::model()->createModel($insert);
    }

    public function offline($params) {
        DB::beginTransaction();
        $adminId = LoginHelper::getAdminId();
        $lastLog = CollectionOnlineLog::model()->newQuery()->where("admin_id", $adminId)->orderByDesc('id')->first();
        $insert = [
            "status" => CollectionOnlineLog::STATUS_OFFLINE,
            "status_value" => isset($params['status_value']) ? $params['status_value'] : "",
            "admin_id" => $adminId,
            "created_at" => DateHelper::dateTime(),
            "ip" => HostHelper::getIp(),
        ];
        if ($lastLog) {
            if (CollectionOnlineLog::STATUS_OFFLINE == $lastLog->status) {
                DB::rollBack();
                return $lastLog;
            } else {
                $insert['online_time'] = $lastLog->created_at;

                # 计算工作实时间
                $lastLog->use_time = time() - strtotime($lastLog->created_at);
                $lastLog->status_value = $insert['status_value'];
                $lastLog->offline_time = DateHelper::dateTime();
                if (isset($params['status_value']) && $params['status_value'] == "timeOut") {
                    $lastLog->warning_num = 1;
                    $lastLog->warning_time = DateHelper::dateTime();
                }
                $lastLog->save();
            }
        }
        $res = CollectionOnlineLog::model()->createModel($insert);
        DB::commit();
        return $res;
    }

    public function status() {
        $adminId = LoginHelper::getAdminId();
        $lastLog = CollectionOnlineLog::model()->newQuery()->where("admin_id", $adminId)->orderByDesc('id')->first();
        if ($lastLog) {
            return $lastLog;
        }
        return $this->outputError("没有最新状态");
    }

    public function warning() {
        return $this->outputError("接口取消");
        $adminId = LoginHelper::getAdminId();
        $lastLog = CollectionOnlineLog::model()->newQuery()->where("admin_id", $adminId)->orderByDesc('id')->first();
        if ($lastLog && $lastLog->status == CollectionOnlineLog::STATUS_ONLINE) {
            $lastLog->warning_num = 1;
            $lastLog->warning_time = DateHelper::dateTime();
            $lastLog->save();
            return $lastLog;
        }
        return $this->outputError("没有最新状态");
    }

    public function reportNew($params) {
        $date = date("Y-m-d");

        $where = " onl.merchant_id='" . MerchantHelper::helper()->getMerchantId() . "'";
        $orderBy = "followed_cases";
        if (isset($params['level_name'])) {
            if(is_array($params['level_name'])){
                $where .= " AND level_name in ('".implode("','",$params['level_name'])."') ";
            }else{
                $where .= " AND level_name='{$params['level_name']}' ";
            }
        }
        if (isset($params['nickname'])) {
            $where .= " AND onl.admin_id in (" . implode(",", $params['nickname']) . ") ";
        }
        if (isset($params['order_by'])) {
            $orderBy = $params['order_by'];
        }
        $newCol = "";
        foreach(CollectionRecord::TIME_SLOT as $key => $timeSlot){
            $newCol .= ", extend".($key+1)." as '{$timeSlot}'";
        }
        $sql = "SELECT onl.id,onl.merchant_id,onl.level_name,onl.nickname,ps.need_collection_count as cases_num, followed_cases, followes, concat(PTP,'/',PTP_PAID) AS PTP, onboard_duration,case when current_status='1' THEN 'Online' ELSE current_status END as current_status,status_duration,break_times,warning,long_breaks,onl.created_at,onl.updated_at{$newCol} FROM statistics_collection_staff_online onl
LEFT JOIN statistics_collection_staff_peso ps ON onl.admin_id=ps.admin_id AND ps.date='{$date}' AND onl.level_name=ps.`level`
WHERE {$where}  having cases_num>0 ORDER BY {$orderBy}";
        return \DB::select($sql);
    }

    public function report($params) {
        $where = " staff.merchant_id='" . MerchantHelper::helper()->getMerchantId() . "' ";
        if (isset($params['level_name']) && $params['level_name']) {
            $where .= " AND level_name='{$params['level_name']}' ";
        }
        $slotSql = "";
        foreach(CollectionRecord::TIME_SLOT as $key => $timeSlot){
            $slotSql .= ",concat((select count(1) from (select collection.admin_id,count(1),collection.`level` from collection INNER join collection_record ON collection_record.collection_id=collection.id where collection_record.promise_paid_time_slot='{$timeSlot}' AND collection_record.progress in('today\'s_commitment') AND collection_record.created_at>DATE(now()) AND collection_record.id=(select id from collection_record cr where cr.collection_id=collection_record.collection_id order by id desc limit 1)
 group by collection.order_id,collection.`level`) a where admin_id=staff.id AND a.`level`=admin.level_name) ,'/',
 (select count(1) from (select collection.admin_id,count(1),collection.`level` from collection INNER join collection_record ON collection_record.collection_id=collection.id where collection_record.promise_paid_time_slot='{$timeSlot}' AND collection_record.progress in('today\'s_commitment', 'not_today_commitment', 'renewal_repayment') AND collection.`status`='collection_success' AND collection_record.created_at>DATE(now()) AND collection_record.id=(select id from collection_record cr where cr.collection_id=collection_record.collection_id order by id desc limit 1)
 group by collection.order_id,collection.`level`) a where admin_id=staff.id AND a.`level`=admin.level_name)) as extend".($key+1);
        }
        $sql = "SELECT 
staff.id as admin_id,
admin.level_name,
concat(staff.username,'_',staff.nickname) as nickname,
0 as cases_num,
(select count(1) from (select collection.admin_id,count(1),collection.`level` from collection INNER join collection_record ON collection_record.collection_id=collection.id where collection_record.created_at>DATE(now())
 group by collection.order_id,collection.`level`) a where admin_id=staff.id AND a.`level`=admin.level_name) as followed_cases,
(select count(1) from collection_record where admin_id=staff.id AND created_at>DATE(now()) AND collection_record.`level`=admin.level_name) as followes,
(select count(1) from (select collection.admin_id,count(1),collection.`level` from collection INNER join collection_record ON collection_record.collection_id=collection.id where  collection_record.progress in('today\'s_commitment') AND collection_record.created_at>DATE(now()) AND collection_record.id=(select id from collection_record cr where cr.collection_id=collection_record.collection_id order by id desc limit 1)
 group by collection.order_id,collection.`level`) a where admin_id=staff.id AND a.`level`=admin.level_name) as PTP,
 (select count(1) from (select collection.admin_id,count(1),collection.`level` from collection INNER join collection_record ON collection_record.collection_id=collection.id where  collection_record.progress in('today\'s_commitment', 'not_today_commitment', 'renewal_repayment') AND collection.`status`='collection_success' AND collection_record.created_at>DATE(now()) AND collection_record.id=(select id from collection_record cr where cr.collection_id=collection_record.collection_id order by id desc limit 1)
 group by collection.order_id,collection.`level`) a where admin_id=staff.id AND a.`level`=admin.level_name) as PTP_PAID,
(select sum(case when use_time>0 then use_time else unix_timestamp()-unix_timestamp(created_at) end) from collection_online_log where admin_id=staff.id AND `status`='1' AND created_at>DATE(now())) as onboard_duration,
(select case when `status`=1 THEN 1 ELSE status_value end from collection_online_log where admin_id=staff.id AND created_at>DATE(now()) ORDER BY id DESC limit 1) as current_status,
(select unix_timestamp()-unix_timestamp(created_at) from collection_online_log where admin_id=staff.id AND created_at>DATE(now()) ORDER BY id DESC limit 1) as status_duration,
(select sum(case when use_time>0 then use_time else unix_timestamp()-unix_timestamp(created_at) end) from collection_online_log where admin_id=staff.id AND `status`='2' AND created_at>DATE(now())) as break_times,
(select sum(warning_num) from collection_online_log where admin_id=staff.id AND created_at>DATE(now())) as warning,
(select count(1) from collection_online_log where admin_id=staff.id AND `status`='2' AND created_at>DATE(now()) AND case when use_time>0 then use_time else unix_timestamp()-unix_timestamp(created_at) end>15*60) as  long_breaks
{$slotSql}
FROM staff
INNER JOIN collection_admin admin ON staff.id=admin_id AND admin.`status`=1
WHERE {$where}
ORDER BY followed_cases";
        return \DB::connection("mysql_readonly")->table(\DB::raw("($sql) list"));
    }

    public function todayFinish() {
        $adminId = LoginHelper::helper()->getAdminId();
        $staff = Staff::model()->getOne($adminId);
        $collectionRoles = ["催收Collection"];
        $approveRoles = ['电话审核员PV'];
        $telephoneRoles = ["电销TELE"];

        $res = [];
        foreach ($staff->roles as $item) {
            if (in_array($item->name, $collectionRoles)) {
                if (Collection::model()->where("admin_id", $adminId)->whereIn("status", Collection::STATUS_NOT_COMPLETE)->exists()) {
                    $res['collection'] = CollectionServer::server()->todayFinish($adminId);
                }
            }
            if (in_array($item->name, $approveRoles)) {
                if (ApproveUserPool::model()->where("admin_id", $adminId)->whereIn('status', [ApproveUserPool::STATUS_CHECKING, ApproveUserPool::STATUS_NO_ANSWER])->exists()) {
                    $res['approve'] = [
                        "target" => ["coverage" => "80", "rate" => "40%"],
                        "your" => (new ApproveService())->todayFinish($adminId)];
                }
            }
            if (in_array($item->name, $telephoneRoles)) {
                if (MarketingPhoneAssign::model()->where("saler_id", $adminId)->where("status", MarketingPhoneAssign::STATUS_NORMAL)->exists()) {
                    $res['marketing'] = [
                        "target" => ["coverage" => "350", "income" => "15"],
                        "your" => CustomerServer::server()->todayFinish($adminId)];
                }
            }
        }
        return $res;
    }

    public function getLastTimeByDate($status, $date, $desc = 'DESC') {
        $query = CollectionOnlineLog::model()->where("status", $status)->where(\DB::raw("date(created_at)"), $date);
        if ($desc == "DESC") {
            $query->orderByDesc("id");
        } else {
            $query->orderBy("id");
        }
        return $query->first();
    }

}
