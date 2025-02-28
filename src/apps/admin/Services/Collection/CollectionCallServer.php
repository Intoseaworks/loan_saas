<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Collection;

use Common\Services\BaseService;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use Common\Models\Call\CallAdmin;
use Common\Models\Call\CallLog;
use Common\Models\Call\CallAutoAssign;

class CollectionCallServer extends BaseService {

    public function list($params) {
        $merchantId = MerchantHelper::helper()->getMerchantId();
        $pageSize = array_get($params, 'page_size', 10);
        $currentPage = array_get($params, 'page', 1);
        if (isset($params['page_size']) && $params['page_size']) {
            $pageSize = $params['page_size'];
        }
        if (isset($params['page']) && $params['page']) {
            $currentPage = $params['page'];
        }
        $limit = " LIMIT " . (($currentPage - 1) * $pageSize) . ", {$pageSize};";
        $where = "";
        if (isset($params['username'])) {
            $where .= " AND staff.username='{$params['username']}' ";
        }
        if (isset($params['type'])) {
            $where .= " AND ca.type='{$params['type']}' ";
        }
        if (isset($params['status'])) {
            $where .= " AND ca.`status`='{$params['status']}' ";
        }
        $sql = "select ca.admin_id,ca.type,staff.username,ca.extension_num,ca.`status` from call_admin ca
INNER JOIN staff ON ca.admin_id=staff.id
WHERE 1 AND staff.merchant_id='{$merchantId}' {$where}
ORDER BY username";
        $res = \DB::select($sql);
        foreach ($res as $item) {
            $item->status_txt = CallAdmin::STATUS[$item->status] ?? "";
            $item->type_txt = CallAdmin::TYPE[$item->type] ?? "";
        }
        return $res;
    }

    public function setStatus($adminID) {
        $merchantId = MerchantHelper::helper()->getMerchantId();
        $call = CallAdmin::model()->newQuery()->where("admin_id", $adminID)->first();
        if (!$call) {
            return $this->outputError("Not exist");
        }
        if ($call->status == CallAdmin::STATUS_NORMAL) {
            $call->status = CallAdmin::STATUS_FORGET;
        } else {
            if (CallAdmin::model()->where("extension_num", $call->extension_num)
                            ->where("status", CallAdmin::STATUS_NORMAL)
                            ->where("merchant_id", $merchantId)
                            ->where("admin_id", "<>", $adminID)->exists()) {
                return $this->outputError("Extension number is occupied");
            }
            $call->status = CallAdmin::STATUS_NORMAL;
        }
        return $this->outputSuccess("Success", $call->save());
    }

    public function create($type, $username, $extensionNum) {
        $admin = \Common\Models\Staff\Staff::model()->getOneByData(['username' => $username]);
        if ($admin) {
            $merchantId = MerchantHelper::helper()->getMerchantId();
            if (CallAdmin::model()->where("extension_num", $extensionNum)
                            ->where("status", CallAdmin::STATUS_NORMAL)
                            ->where("merchant_id", $merchantId)
                            ->exists()) {
                return $this->outputError("Extension number is occupied");
            }
            $res = CallAdmin::model()->updateOrCreateModel([
                "type" => $type,
                "admin_id" => $admin->id,
                "merchant_id" => $merchantId,
                //"status" => CallAdmin::STATUS_NORMAL,
                "extension_num" => $extensionNum,
                    ], ["admin_id" => $admin->id]);
            if ($res) {
                return $this->outputSuccess();
            }
            return $this->outputError("Failed to create");
        }
        return $this->outputError("Username does not exist");
    }

    public function call($params, $extensionNum = '', $adminId = 0, $approvePool = 0, $mp3Path='') {
        $callType = CallAdmin::TYPE_TEST;
        if ($adminId == 0) {
            $adminId = LoginHelper::getAdminId();
            $call = CallAdmin::model()->newQuery()->where("admin_id", $adminId)->where("status", CallAdmin::STATUS_NORMAL)->get()->first();
            if ($call) {
                $extensionNum = $call->extension_num;
                $callType = $call->type;
            }
        }
        if(isset($params['type']) && $params['type']){
            $callType = $params['type'];
        }
        $telephone = array_get($params, 'telephone');

        if ($extensionNum && $telephone) {
            #执行呼叫接口
//            $senbac = new SENBAC();
//            return $senbac->initConfig(app()->environment(), MerchantHelper::getMerchantId())->send($call->extension_num, $telephone);
            if ($approvePool) {
                $uuid = \Common\Utils\CallCenter\Freeswitchesl::factory(app()->environment(), MerchantHelper::getMerchantId())->callToNumberApprovePool($extensionNum, $telephone);
            } else {
                $uuid = \Common\Utils\CallCenter\Freeswitchesl::factory(app()->environment(), MerchantHelper::getMerchantId())->callToNumber($extensionNum, $telephone, $mp3Path);
            }
            if ($uuid) {
                $insert = [
                    "admin_id" => $adminId,
                    "type" => $callType ?? 0,
                    "extension_num" => $extensionNum,
                    "uuid" => $uuid,
                    "telephone" => $telephone,
                    //"json_detail" => $this->data,
                    "order_id" => array_get($params, 'order_id', 0),
                    "customer_id" => array_get($params, 'customer_id', 0),
                ];
                CallLog::model()->updateOrCreateModel($insert, ["uuid" => $uuid]);
                return $this->outputSuccess("Dialing", $uuid);
            } else {
                return $this->outputError("Call failure");
            }
        } else {
            return $this->outputError("The user is disabled or has not set a call");
        }
    }

    public function file($params) {
        $merchantId = MerchantHelper::helper()->getMerchantId();
        $pageSize = array_get($params, 'page_size', 10);
        $currentPage = array_get($params, 'page', 1);
        if (isset($params['page_size']) && $params['page_size']) {
            $pageSize = $params['page_size'];
        }
        if (isset($params['page']) && $params['page']) {
            $currentPage = $params['page'];
        }
        $limit = " LIMIT " . (($currentPage - 1) * $pageSize) . ", {$pageSize};";
        $where = " length(cf.ext)=4 ";
        if (isset($params['telephone'])) {
            $where .= " AND cf.telephone='{$params['telephone']}' ";
        }
        if (isset($params['ext'])) {
            $where .= " AND cf.ext='{$params['ext']}' ";
        }
        if (isset($params['date']) && is_array($params)) {
            $where .= " AND date(cf.`call_time`)>='{$params['date'][0]}' AND date(cf.`call_time`)<='{$params['date'][1]}' ";
        }
        if (isset($params['merchant_id'])){
            $where .= " AND cf.ext in (select extension_num from call_admin where merchant_id='{$params['merchant_id']}')";
        }
        $sql = "select cf.filename,call_time,case when cl.extension_num then cl.extension_num else cf.ext end as ext,cf.call_time as created_at,cf.updated_at,cl.variable_billsec as size,cf.telephone,cl.variable_billsec as duration_second from call_file cf
left join call_log cl ON cl.telephone=concat('0', cf.telephone) AND abs(TIMESTAMPDIFF(SECOND,cl.start_time,cf.call_time))<5  AND variable_sip_user_agent='Gateway' AND variable_billsec>0
WHERE {$where}
ORDER BY cf.call_time DESC";
        //$res = \DB::select("select * from call_file");
        $data['total'] = 100000;
        $data['list'] = \DB::select($sql . $limit);

        foreach ($data['list'] as $item) {
            $item->url = \Common\Models\Call\CallFile::HTTP_HOST . $item->filename;
        }
        $data['page_total'] = ceil($data['total'] / $pageSize);
        $data['current_page'] = $currentPage;
        $data['page_size'] = $pageSize;
        return $data;
    }

    public function fileIndex($params) {
        $pageSize = array_get($params, 'page_size', 10);
        $currentPage = array_get($params, 'page', 1);
        if (isset($params['page_size']) && $params['page_size']) {
            $pageSize = $params['page_size'];
        }
        if (isset($params['page']) && $params['page']) {
            $currentPage = $params['page'];
        }
        $limit = " LIMIT " . (($currentPage - 1) * $pageSize) . ", {$pageSize};";
        $where = " 1 ";
        if (isset($params['ext'])) {
            $where .= " AND extension_num='{$params['ext']}' ";
        }
        if (isset($params['date']) && is_array($params)) {
            $where .= " AND date(`call_date`)>='{$params['date'][0]}' AND date(`call_date`)<='{$params['date'][1]}' ";
        }
        if (isset($params['merchant_id'])) {
            $where .= " AND merchant_id={$params['merchant_id']} ";
        }
        $sql = "select *, round(answered/call_total * 100,2) as completing_rate from statistics_call_recording where {$where} order by call_date desc";
        $res = \DB::select($sql);
        $data['total'] = count($res);
        $data['list'] = \DB::select($sql . $limit);

        $data['page_total'] = ceil($data['total'] / $pageSize);
        $data['current_page'] = $currentPage;
        $data['page_size'] = $pageSize;
        return $data;
    }

    public function autoCallList($params) {
        $query = CallAutoAssign::model()->newQuery()->where("status", "1");
        if (isset($params['level_name']) && is_array($params['level_name'])) {
            $query->whereIn("collection_level", $params['level_name']);
        }
        if (isset($params['admin_id']) && is_array($params['admin_id'])) {
            $query->where(function ($query) use ($params) {
                foreach ($params['admin_id'] as $adminId) {
                    $query->orWhere("assign_admin_ids", "like", "%\"{$adminId}\"%");
                }
            });
        }
        return $query->get();
    }

    public function autoCallSave($params) {
        $data = [
            "merchant_id" => MerchantHelper::getMerchantId(),
            "collection_level" => $params["level_name"],
            "assign_admin_ids" => json_encode($params["assign_admin_ids"]),
        ];
        $res = CallAutoAssign::model()->updateOrCreateModel($data, ["collection_level" => $data['collection_level'], "status" => '1', "merchant_id" => $data['merchant_id']]);
        if ($res) {
            return $this->outputSuccess();
        }
        return $this->outputError("fail to save");
    }

    public function autoCallRemove($levelName) {
        CallAutoAssign::model()->where("collection_level", $levelName)->update(["status" => 2]);
        return $this->outputSuccess();
    }

    public function getNextAdminId($levelName) {
        $callAutoAssign = CallAutoAssign::model()->where("collection_level", $levelName)->where("status", "1")->first();
        if ($callAutoAssign) {
            if ($callAutoAssign->next_assign_id) {
                return $callAutoAssign->next_assign_id;
            } else {
                $admins = json_decode($callAutoAssign->assign_admin_ids, true);
                if (is_array($admins)) {
                    $adminIds = array_keys($admins);
                    sort($adminIds);
                    if ($adminIds) {
                        $callAutoAssign->next_assign_id = $adminIds[0];
                        $callAutoAssign->save();
                        return $callAutoAssign->next_assign_id;
                    }
                }
            }
        }
        return null;
    }

    public function setNextAdminId($levelName) {
        $callAutoAssign = CallAutoAssign::model()->where("collection_level", $levelName)->where("status", "1")->first();
        if ($callAutoAssign) {
            if ($callAutoAssign->next_assign_id) {
                $admins = json_decode($callAutoAssign->assign_admin_ids, true);
                if (is_array($admins)) {
                    $adminIds = array_keys($admins);
                    sort($adminIds);
                    if ($adminIds) {
                        foreach ($adminIds as $key => $item) {
                            if ($callAutoAssign->next_assign_id == $item) {
                                if ($key + 1 > count($adminIds) - 1) {
                                    $callAutoAssign->next_assign_id = $adminIds[0];
                                } else {
                                    $callAutoAssign->next_assign_id = $adminIds[$key + 1];
                                }
                                $callAutoAssign->save();
                                return $callAutoAssign->next_assign_id;
                            }
                        }
                    }
                }
            }
            return $this->getNextAdminId($levelName);
        }
        return false;
    }

}
