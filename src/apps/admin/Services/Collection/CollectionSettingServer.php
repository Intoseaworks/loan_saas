<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Collection;

use Admin\Models\Collection\CollectionSetting;
use Admin\Models\Staff\Staff;
use Admin\Services\BaseService;
use Common\Models\Config\Config;
use Common\Models\Collection\CollectionAdmin;
use Common\Models\Email\EmailUser;

class CollectionSettingServer extends BaseService
{
    /**
     * @param $param
     * @return mixed
     */
    public function getSetting($param)
    {
        $data = CollectionSetting::model()->getSetting(array_get($param, 'key'));
        if (!$data) {
            $data = [];
        }
        $rule = json_decode(array_get($data, 'rule', '{}'), true);
        /* if ($rule = array_get($data, 'rule')) {
            $data['rule'] = $this->getRuleAdminName($rule);
          } */
        for ($i = 0; $i < count($rule); $i++) {
            $query = CollectionAdmin::model()->where("level_name", "=", "{$rule[$i]['overdue_level']}")->join("staff", function($join) {
                $join->on("staff.id", "=", "collection_admin.admin_id");
                $join->where("staff.status", '=', '1');
            });
            $query->where('collection_admin.status','=','1');
            $rule[$i]['number_of_collectors'] = $query->count();
        }
        $data['rule'] = $rule;
        $data[Config::KEY_COLLECTION_BAD_DAYS] = Config::getValueByKey(Config::KEY_COLLECTION_BAD_DAYS);
        $data[Config::KEY_COLLECTION_MAX_DEDUCTION_AMOUNT_PER_DAY] = Config::getValueByKey(Config::KEY_COLLECTION_MAX_DEDUCTION_AMOUNT_PER_DAY);
        $data[Config::KEY_COLLECTION_MAX_DEDUCTION_USERS_PER_DAY] = Config::getValueByKey(Config::KEY_COLLECTION_MAX_DEDUCTION_USERS_PER_DAY);
        $data[Config::KEY_COLLECTION_MAX_DEDUCTION_SMS_MESSAGES_PER_ORDER] = Config::getValueByKey(Config::KEY_COLLECTION_MAX_DEDUCTION_SMS_MESSAGES_PER_ORDER);
        $data[Config::KEY_COLLECTION_MAX_DEDUCTION_STAFF_SMS_AMOUNT_PER_DAY] = Config::getValueByKey(Config::KEY_COLLECTION_MAX_DEDUCTION_STAFF_SMS_AMOUNT_PER_DAY);
        return $data;
    }

    public function getLevel()
    {
        $rule = json_decode(CollectionSetting::model()->getSettingVal(CollectionSetting::KEY_RULE), true);
        if (!$rule) {
            $rule = [];
        }
        return array_pluck($rule, 'overdue_level');
    }

    /**
     * 查询催收员用户名
     *
     * @param $rule
     * @return mixed
     */
    public function getRuleAdminName($rule)
    {
        $ruleDatas = json_decode($rule, true);
        $allAdminIds = [];
        foreach ($ruleDatas as $ruleData) {
            $allAdminIds = array_merge($allAdminIds, $ruleData['admin_ids']);
        }
        $staffs = Staff::model()->getNamesByAdminIds($allAdminIds);
        foreach ($ruleDatas as $ruleKey => $ruleVal) {
            if ($adminIds = array_get($ruleVal, 'admin_ids')) {
                foreach ($adminIds as $adminId) {
                    if ($name = array_get($staffs, $adminId)) {
                        $ruleDatas[$ruleKey]['admin'][] = [
                            'id' => $adminId,
                            'name' => $name,
                        ];
                    }
                }
            }
        }
        return $ruleDatas;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getOne($id)
    {
        return CollectionSetting::model()->getOne($id);
    }

    public function create($param)
    {

    }

    public function rule($param)
    {
        //分单配置
        if ($rule = array_get($param, CollectionSetting::KEY_RULE)) {
            foreach ($rule as $ruleKey => $ruleVal){
                $rule[$ruleKey] = array_only($rule[$ruleKey], ['auto_reassign','start_overdue_days','p_period_days' ,'overdue_days', 'overdue_level', 'contact_num', 'admin_ids', 'admin_rules', 'reduction_setting', 'email_user', 'target_type', 'target_value']);
            }
            CollectionSetting::model()->updateSetting(CollectionSetting::KEY_RULE, $rule, false);
        }
        //坏账配置
        if ($overdueDaysToBad = array_get($param, Config::KEY_COLLECTION_BAD_DAYS)) {
            Config::createOrUpdate(Config::KEY_COLLECTION_BAD_DAYS, $overdueDaysToBad);
        }
        //单一账户每日最多申请减免数
        if ($deductionDays = array_get($param, Config::KEY_COLLECTION_MAX_DEDUCTION_USERS_PER_DAY)) {
            Config::createOrUpdate(Config::KEY_COLLECTION_MAX_DEDUCTION_USERS_PER_DAY, $deductionDays);
        }
        //单一账户每日最大可申请减免金额
        if ($deductionAmount = array_get($param, Config::KEY_COLLECTION_MAX_DEDUCTION_AMOUNT_PER_DAY)) {
            Config::createOrUpdate(Config::KEY_COLLECTION_MAX_DEDUCTION_AMOUNT_PER_DAY, $deductionAmount);
        }
        //单笔订单做多可发催收短信数
        if ($deductionSmsOrders = array_get($param, Config::KEY_COLLECTION_MAX_DEDUCTION_SMS_MESSAGES_PER_ORDER)) {
            Config::createOrUpdate(Config::KEY_COLLECTION_MAX_DEDUCTION_SMS_MESSAGES_PER_ORDER, $deductionSmsOrders);
        }
        //单个催收员每日最多可发催收短信数
        if ($deductionSmsStaff = array_get($param, Config::KEY_COLLECTION_MAX_DEDUCTION_STAFF_SMS_AMOUNT_PER_DAY)) {
            Config::createOrUpdate(Config::KEY_COLLECTION_MAX_DEDUCTION_STAFF_SMS_AMOUNT_PER_DAY, $deductionSmsStaff);
        }
        return true;
    }
    
    public function getEmailList(){
        return EmailUser::model()->newQuery()->orderByDesc('id')->get();
    }

}
