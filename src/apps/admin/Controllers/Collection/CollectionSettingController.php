<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\Collection;

use Admin\Controllers\BaseController;
use Admin\Rules\Collection\CollectionSettingRule;
use Admin\Services\Collection\CollectionSettingServer;
use Illuminate\Support\Facades\DB;
use Common\Models\Collection\CollectionAdmin;
use Common\Console\Services\Collection\CollectionAssignServer;
use Common\Utils\MerchantHelper;
use Common\Services\Collection\CollectionAssignJobServer;

class CollectionSettingController extends BaseController {

    public function index() {
        $param = $this->request->all();
        $data = CollectionSettingServer::server()->getSetting($param);
        return $this->resultSuccess($data);
    }

    public function create(CollectionSettingRule $rule) {
        $param = $this->request->all();
        if (!$rule->validate(CollectionSettingRule::SCENARIO_CREATE, $param)) {
            return $this->resultFail($rule->getError());
        }
        CollectionSettingServer::server()->create($param);
        return $this->resultSuccess();
    }

    public function rule(CollectionSettingRule $rule) {
        /* $rule = [
          [
          'overdue_days' => 5,
          'overdue_level' => 'S1',
          'contact_num' => 20,
          'admin' => [1,2],
          'reduction_setting' => 'cannot',
          ],
          [
          'overdue_days' => 10,
          'overdue_level' => 'S2',
          'contact_num' => 20,
          'admin' => [3,4],
          'reduction_setting' => 'overdue_interest',
          ],
          [
          'overdue_days' => 20,
          'overdue_level' => 'S3',
          'contact_num' => 20,
          'admin' => [5,6],
          'reduction_setting' => 'principal_interest',
          ],
          ];
          echo json_encode($rule, 256);exit(); */
        $param = $this->request->all();
        if (!$rule->validate(CollectionSettingRule::SCENARIO_CREATE_RULE, $param)) {
            return $this->resultFail($rule->getError());
        }
        foreach($param['rule'] as $item){
        if($item['target_type'] == 'rate' && (!is_numeric($item['target_value']) || $item['target_value']>100 || $item['target_value']<0)){
                return $this->resultFail("The target value must be between 0 and 100");
            }
        }
        CollectionSettingServer::server()->rule($param);
        return $this->resultSuccess();
    }

    public function reallocation() {
        $params = $this->request->all();
        $levelName = array_get($params, 'index', '');
        $ptp = array_get($params, 'type', '') == 'resetPTP' ? true : false;
        if ($levelName) {
            $res = CollectionAssignJobServer::server()->assign($levelName, $ptp);
            if ($res->isSuccess()) {
                return $this->resultSuccess();
            } else {
                return $this->resultFail($res->getMsg());
            }
        } else {
            return $this->resultFail('missing parameter ');
        }
    }

    public function adminList() {
        $params = $this->request->all();
        $levelName = array_get($params, 'level_name');
        $merchantId = MerchantHelper::getMerchantId();
        $sql = "select s.*,ca.level_name,ca.language,ca.weight,ca.id as cid from collection_admin ca INNER JOIN staff s ON ca.admin_id=s.id WHERE ca.`level_name` = '{$levelName}' AND ca.status='1' AND s.`status`<>-1 AND s.merchant_id='{$merchantId}'";
        $res = DB::select($sql);
        return $this->resultSuccess($res);
    }

    public function updateWeight() {
        $params = $this->request->all();
        $cid = array_get($params, 'cid');
        $weight = array_get($params, 'weight');
        $cAdmin = CollectionAdmin::model()->getOne($cid);
        $cAdmin->weight = $weight;
        $cAdmin->save();
        return $this->resultSuccess();
    }

    public function removeFromGroup() {
        $params = $this->request->all();
        $cid = array_get($params, 'id');
        $levelName = array_get($params, 'level_name');

        $cAdmin = CollectionAdmin::model()->getOne($cid);
        $cAdmin->status = 2;
        $cAdmin->save();
        return $this->resultSuccess();
    }
    
    public function emailList(){
        return $this->resultSuccess(CollectionSettingServer::server()->getEmailList());
    }

}
