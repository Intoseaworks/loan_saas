<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Crm;

use Admin\Imports\Crm\WhiteListImport;
use Admin\Models\User\UserBlack;
use Admin\Services\BaseService;
use Common\Models\Crm\CrmWhiteBatch;
use Common\Models\Crm\CrmWhiteFailed;
use Common\Models\Crm\CrmWhiteList;
use Common\Models\Crm\Customer;
use Common\Models\Risk\RiskBlacklist;
use Common\Utils\LoginHelper;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Common\Services\Rbac\Models\Role;
use Illuminate\Support\Facades\DB;
use Common\Utils\MerchantHelper;

class WhiteListServer extends BaseService {

    const EXCEL_RULE = [
        'telephone' => 'required|mobile',
        'type' => 'required',
    ];

    public function upload($batchInfo) {
        $res = Excel::toArray(new WhiteListImport, $batchInfo['file']);
//        print_r($res);
        //作废老的同名批次
        if (CrmWhiteBatch::model()->newQuery()->where("batch_number", $batchInfo['batch_number'])->exists()) {
            return $this->outputError("Batch number already exist!");
        }
        $indate = date("Y-m-d H:i:s", time() + 86400 * $batchInfo['days']);
        $batch = CrmWhiteBatch::model()->createModel([
            "merchant_id" => MerchantHelper::getMerchantId(),
            "batch_number" => $batchInfo['batch_number'],
            "indate" => $indate,
            "match_rule" => $batchInfo['match_rule'],
            "match_blacklist" => $batchInfo['match_blacklist'],
            "match_greylist_type" => $batchInfo['match_greylist_type'],
            "total_count" => 0,
            'admin_id' => LoginHelper::getAdminId()
        ]);
        dispatch(new \Common\Jobs\Crm\UploadWhitelistJob($batch, $res, LoginHelper::getAdminId()));
        return $this->outputSuccess("success " . count($res[0]) . "!");
    }

    public function setBatchStatus($params) {
        switch ($params['action']) {
            case "delete":
                CrmWhiteBatch::model()->newQuery()->whereIN("id", $params['id_list'])->update(['status' => CrmWhiteBatch::STATUS_FORGET]);
                break;
            case "using":
                CrmWhiteBatch::model()->newQuery()->whereIN("id", $params['id_list'])->update(['status' => CrmWhiteBatch::STATUS_NORMAL]);
                break;
        }
        return $this->outputSuccess();
    }

    public function setWhiteListStatus($params) {
        switch ($params['action']) {
            case "delete":
                CrmWhiteList::model()->newQuery()->whereIN("id", $params['id_list'])->update(['status' => CrmWhiteBatch::STATUS_FORGET]);
                break;
            case "using":
                CrmWhiteList::model()->newQuery()->whereIN("id", $params['id_list'])->update(['status' => CrmWhiteBatch::STATUS_NORMAL]);
                break;
        }
        return $this->outputSuccess();
    }

    /**
     * 
     * @param type $params
     */
    public function setBatchPostpone($params) {
        //$indate = date("Y-m-d H:i:s", time() + 86400 * $params['days']);
        CrmWhiteBatch::model()->newQuery()->whereIN("id", $params['id_list'])->update(["indate" => DB::raw("date_add(indate, INTERVAL {$params['days']} DAY)")]);
        CrmWhiteList::model()->newQuery()->whereIN("batch_id", $params['id_list'])->update(["indate" => DB::raw("date_add(indate, INTERVAL {$params['days']} DAY)")]);
        return $this->outputSuccess();
    }

    public function setPostpone($params) {
        //$indate = date("Y-m-d H:i:s", time() + 86400 * $params['days']);
        CrmWhiteList::model()->newQuery()->whereIN("id", $params['id_list'])->update(["indate" => DB::raw("date_add(indate, INTERVAL {$params['days']} DAY)")]);
        return $this->outputSuccess();
    }

    public function list($params) {
        $size = array_get($params, 'page_size');
        $query = CrmWhiteList::model()->newQuery()->where("merchant_id", MerchantHelper::getMerchantId());
        if (isset($params['batch_number']) && $params['batch_number']) {
            $keyword = $params['batch_number'];
            $query->whereHas('crmWhiteBatch', function ($batch) use ($keyword) {
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
            $item->batch_no = $item->crmWhiteBatch->batch_number;
            $item->operator_name = $item->operator->nickname ?? "";
            $item->status_txt = t(CrmWhiteList::STATUS[$item->status], "crm");
            $item->customer_status_txt = isset($item->customerStatus()->status) ? t(Customer::STATUS_CUSTOMER[$item->customerStatus()->status], "crm") : "";
        }
        return $res;
    }

    public function listBatch($params) {
        $size = array_get($params, 'page_size');
        $query = CrmWhiteBatch::model()->newQuery()->where("merchant_id", MerchantHelper::getMerchantId());
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

    public function checkBlackList($item) {
        $query = RiskBlacklist::model()->newQuery();
        $query->where("value", $item['telephone']);
        $where = false;
        if (isset($item['email']) && $item['email']) {
            $where = true;
            $query->orWhere("value", $item['email']);
        }
        if (isset($item['id_number']) && $item['id_number']) {
            $where = true;
            $query->orWhere("value", $item['id_number']);
        }
        if($where){
            return $query;
        }else{
            return $query->where("id","-1");
        }
    }

    /**
     * 入库customer
     * @param CrmWhiteList $whiteList
     */
    public function intoCustomer(CrmWhiteList $whiteList) {
        $wheres = [
            "id_number" => ['id_type' => $whiteList->id_type, "id_number" => $whiteList->id_number],
            "fullname" => ["telephone" => $whiteList->telephone, "fullname" => $whiteList->fullname],
            "birthday" => ["birthday" => $whiteList->birthday, "fullname" => $whiteList->fullname],
            "telephone" => ["telephone" => $whiteList->telephone],
            "email" => ["email" => $whiteList->email],
        ];
        $attributes = [
            "telephone" => $whiteList->telephone,
            "email" => $whiteList->email,
            "fullname" => $whiteList->fullname,
            "birthday" => $whiteList->birthday,
            "id_type" => $whiteList->id_type,
            "id_number" => $whiteList->id_number,
            "batch_id" => $whiteList->batch_id,
            "type" => Customer::TYPE_WHITELIST,
            "remark" => $whiteList->remark
        ];
        $isSave = true;
        foreach ($wheres as $key => $where) {
            if ($whiteList->$key) {
                $query = Customer::model()->newQuery();
                $res = $query->where($where)->get()->first();
                if ($res) {
                    $isSave = false;
                    $whiteList->customer_id = $res->id;
                    $whiteList->save();
                    \Common\Models\Crm\CustomerStatus::model()->updateOrCreateModel([
                        "merchant_id" => MerchantHelper::getMerchantId(),
                        "customer_id" => $res->id,
                        "batch_id" => $whiteList->batch_id,
                        "type" => Customer::TYPE_WHITELIST,
                        "remark" => $whiteList->remark
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
                "batch_id" => $whiteList->batch_id,
                "type" => Customer::TYPE_WHITELIST,
                "remark" => $whiteList->remark
                    ], [
                "merchant_id" => MerchantHelper::getMerchantId(),
                "customer_id" => $customer->id,
            ]);
            $whiteList->customer_id = $customer->id;
            $whiteList->save();
        }
    }

    /**
     * 取出<销售>字样角色人员作为催收人员
     */
    public function getSaler() {
        /** 取出<销售>字样角色 */
        $roleQuery = Role::where('deleted_at', null);
        $roleQuery->where(function ($query) {
            $query->where('name', 'like', '%销售%');
            $query->orWhere('name', 'like', '%saler%');
            $query->orWhere('name', 'like', '%营销%');
            $query->orWhere('name', 'like', '%tel%');
        });
        $roleIds = $roleQuery->pluck('id')->toArray();
        $userArr = array_get((new Role)->roleUserList(999, $roleIds), 'data');
        $data = [];
        foreach ($userArr as $user) {
            $data[$user['id']] = $user['username'];
        }
        return $data ?? [];
    }

}
