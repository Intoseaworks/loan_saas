<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Crm;

use Admin\Models\User\UserBlack;
use Admin\Services\BaseService;
use Carbon\Carbon;
use Common\Models\Crm\Customer;
use Common\Models\Crm\CustomerEmail;
use Common\Models\Crm\CustomerFb;
use Common\Models\Crm\CustomerPaper;
use Common\Models\Crm\CustomerTelephone;
use Common\Models\Crm\MarketingPhoneLog;
use Common\Models\Crm\MarketingSmsLog;
use Common\Services\NewClm\ClmCustomerServer;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Third\AirudderHelper;
use Risk\Common\Models\Third\ThirdDataAirudder;
use Common\Models\Crm\CustomerStatus;

class CustomerServer extends BaseService {

    public function list($params) {
        $size = array_get($params, 'size');
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
                . "crm_customer.telephone,"
                . "crm_customer.telephone_status,"
                . "crm_customer.telephone_check_time,"
                . "crm_customer.email,"
                . "crm_customer.fullname,"
                . "crm_customer.birthday,"
                . "crm_customer.id_type,"
                . "crm_customer.id_number,"
                . "crm_customer.gender"));
        if (isset($params['telephone'])) {
            $query->where("telephone", $params['telephone']);
        }
        if (isset($params['id_number'])) {
            $query->where("id_number", $params['id_number']);
        }
        if (isset($params['telephone_status'])) {
            $query->where("telephone_status", $params['telephone_status']);
        }
        if (isset($params['customer_status'])) {
            $query->where("crm_customer_status.status", $params['customer_status']);
        }
        if (isset($params['type'])) {
            $query->where("crm_customer_status.type", $params['type']);
        }
        $query->orderByDesc("id");
        return $query->paginate($size);
    }

    public function view($id) {
        $customer = Customer::model()->getOne($id);
        if (!$customer) {
            $this->outputException("客户不存在");
        }
        $customerStatus = \Common\Models\Crm\CustomerStatus::model()->getStatus($customer->id);
        if($customerStatus){
            $customer['clm_level'] = $customerStatus->clm_level;
            $customer['main_user_id'] = $customerStatus->main_user_id;
            $customer['type'] = $customerStatus->type;
            $customer['status'] = $customerStatus->status;
            $customer['batch_id'] = $customerStatus->batch_id;
            $customer['suggest_time'] = $customerStatus->suggest_time;
            $customer['last_login_time'] = $customerStatus->last_login_time;
            $customer['max_overdue_days'] = $customerStatus->max_overdue_days;
            $customer['status_stop_days'] = $customer->getStatusStopDays();
            $customer['settle_times'] = $customerStatus->settle_times;
            $customer['last_settle_time'] = $customerStatus->last_settle_time;
            $customer['status_updated_time'] = $customerStatus->status_updated_time;
            $customer['remark'] = $customerStatus->remark;
        }
        $customer['reg_time'] = isset($customer->user) ? $customer->user->created_at->toDateTimeString() : '';
        $customer['status_txt'] = isset($customer->status) ? t(Customer::STATUS_CUSTOMER[$customer->status], "crm") : "";
        $customer['telephone_status_txt'] = isset(Customer::TELEPHONE_STATUS_LIST[$customer->telephone_status]) ? t(Customer::TELEPHONE_STATUS_LIST[$customer->telephone_status], "crm") : "";
        $customer['type_txt'] = t(Customer::TYPE_LIST[$customer->type], "crm");
        $lastSms = MarketingSmsLog::model()->newQuery()->where("customer_id", $id)->orderByDesc('id')->first();
        $lastPhone = MarketingPhoneLog::model()->newQuery()->where("customer_id", $id)->orderByDesc('id')->first();
        $customer['last_marketing_time_sms'] = $lastSms ? $lastSms->created_at->toDateTimeString() : '';
        $customer['last_marketing_time_phone'] = $lastPhone ? $lastPhone->created_at->toDateTimeString() : "";
        $customer['fb'] = CustomerFb::model()->newQuery()->where('customer_id', $id)->get()->toArray();
        $customer['emails'] = CustomerEmail::model()->newQuery()->where('customer_id', $id)->get()->toArray();
        $customer['telephones'] = CustomerTelephone::model()->newQuery()->where('customer_id', $id)->get()->toArray();
        $customer['paper'] = CustomerPaper::model()->newQuery()->where('customer_id', $id)->get()->toArray();
        $customer["is_blacklist"] = $this->isBlack($customer);
        $customer["is_greylist"] = UserBlack::model()->isActive()->whereTelephone($customer->telephone)->exists();
        $customer['is_whitelist'] = $customer->type == Customer::TYPE_WHITELIST;
        $customer['user_info'] = $customer->userInfo;

        $customer['coupon_interest_free_count'] = $this->getAvailableCouponCount($customer, 1);
        $customer['coupon_voucher_count'] = $this->getAvailableCouponCount($customer, 2);
        $customer['coupon_voucher_limit'] = $this->getDeductibleCouponAmount($customer);

        $customer['clm_limit'] = 0;
        $customer['clm_open_rate'] = 0;
        if (isset($customer->user)) {
            try {
                $clmCustomer = ClmCustomerServer::server()->getCustomer($customer->user);
            } catch (\Exception $e) {
                //echo "CLM" . $e->getMessage() . PHP_EOL;
            }
        }
        if (isset($clmCustomer)) {
            $customer['clm_limit'] = $clmCustomer->calcAvailableAmount();
            $customer['clm_open_rate'] = $clmCustomer->getCurrentLevelAmount()->clm_interest_discount;
        }

        $customer['name_birthday_list'] = \DB::select("SELECT u.fullname,birthday,u.created_at FROM crm_customer_user_map map
INNER JOIN `user` u ON map.user_id=u.id
LEFT JOIN user_info ui ON map.user_id=ui.user_id
WHERE map.customer_id={$id}");
//        $customer['user_info'] =
        return $customer;
    }

    public function addFb($params) {
        $data = [
            "customer_id" => $params['customer_id'],
            "reg_telephone" => $params['reg_telephone'],
            "fb_id" => $params['fb_id'],
            "admin_id" => LoginHelper::getAdminId(),
        ];
        if (isset($params['id'])) {
            $res = CustomerFb::model()->updateOrCreateModel($data, ["id" => $params['id']]);
        } else {
            $res = CustomerFb::model()->createModel($data);
        }
        if ($res) {
            return $this->outputSuccess();
        }
        return $this->outputException("保存失败");
    }

    public function addTelephone($params) {
        $telephoneStatus = CustomerTelephone::TELEPHONE_STATUS_UNDETECTED;
        $airudderStatus = ThirdDataAirudder::model()->check($params['telephone']);
        if ($airudderStatus == null) {
            //AirudderHelper::helper()->query($params['telephone']);
        }
        if ($airudderStatus) {
            $telephoneStatus = CustomerTelephone::TELEPHONE_STATUS_STOP;
        } else {
            $telephoneStatus = CustomerTelephone::TELEPHONE_STATUS_NORMAL;
        }
        $data = [
            "customer_id" => $params['customer_id'],
            "telephone" => $params['telephone'],
            "telephone_status" => $telephoneStatus,
            "admin_id" => LoginHelper::getAdminId()
        ];
        if (isset($params['id'])) {
            $res = CustomerTelephone::model()->updateOrCreateModel($data, ["id" => $params['id']]);
        } else {
            $res = CustomerTelephone::model()->createModel($data);
        }
        if ($res) {
            return $this->outputSuccess();
        }
        return $this->outputException("保存失败");
    }

    public function addEmail($params) {
        $data = [
            "customer_id" => $params['customer_id'],
            "email" => $params['email'],
            "admin_id" => LoginHelper::getAdminId()
        ];
        if (isset($params['id'])) {
            $res = CustomerEmail::model()->updateOrCreateModel($data, ["id" => $params['id']]);
        } else {
            $res = CustomerEmail::model()->createModel($data);
        }
        if ($res) {
            return $this->outputSuccess();
        }
        return $this->outputException("保存失败");
    }

    public function addPaper($params) {
        $data = [
            "customer_id" => $params['customer_id'],
            "id_type" => $params['id_type'],
            "id_number" => $params['id_number'],
            "admin_id" => LoginHelper::getAdminId()
        ];
        if (isset($params['id'])) {
            $res = CustomerPaper::model()->updateOrCreateModel($data, ["id" => $params['id']]);
        } else {
            $res = CustomerPaper::model()->createModel($data);
        }
        if ($res) {
            return $this->outputSuccess();
        }
        return $this->outputException("保存失败");
    }

    public function setMainTelephone($id) {
        $tel = CustomerTelephone::model()->getOne($id);
        if ($tel) {
            $customer = Customer::model()->getOne($tel->customer_id);
            $customer->telephone = $tel->telephone;
            $customer->save();
            return $this->outputSuccess();
        } else {
            $this->outputError("Record does not exist");
        }
    }

    public function setTelephoneStatus($id, $status) {
        $tel = CustomerTelephone::model()->getOne($id);
        if ($tel) {
            $tel->status = $status == "1" ? CustomerTelephone::STATUS_NORMAL : CustomerTelephone::STATUS_FORGET;
            $tel->save();
            return $this->outputSuccess();
        } else {
            $this->outputError("Record does not exist");
        }
    }

    public function isBlack(Customer $customer) {
        $item = [
            "telephone" => $customer->telephone,
            "email" => $customer->email,
            "id_number" => $customer->id_number
        ];
        return WhiteListServer::server()->checkBlackList($item)->count() > 0;
    }

    //获取可用优惠券张数,type=1 免息券 2 抵扣券

    /**
     * @param $type string
     */
    public function getAvailableCouponCount(Customer $customer,$type)
    {
        if($customer->main_user_id == 0){
            return 0;
        }
        return $customer->coupons()->wherePivot('use_time', '=','0000-00-00 00:00:00')
            ->where('coupon_type','=',$type)->where('status','=',1)
            ->where('end_time','>',Carbon::now()->toDateTimeString())->count();
    }

    public function getDeductibleCouponAmount(Customer $customer)
    {
        if($customer->main_user_id == 0){
            return 0;
        }
        return $customer->coupons()->wherePivot('use_time', '=','0000-00-00 00:00:00')
            ->where('coupon_type','=',2)->where('status','=',1)
            ->where('end_time','>',Carbon::now()->toDateTimeString())->sum('used_amount');
    }
}
