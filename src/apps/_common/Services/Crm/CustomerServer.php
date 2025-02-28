<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Common\Services\Crm;

use Api\Services\Order\OrderServer;
use Api\Services\Third\AirudderServer;
use Common\Models\Crm\Customer;
use Common\Models\User\User;
use Common\Models\Order\Order;
use Common\Models\Login\UserLoginLog;
use Common\Services\BaseService;
use Common\Services\NewClm\ClmCustomerServer;
use Illuminate\Database\Eloquent\Builder;
use Common\Models\Crm\CustomerUserMap;
use Common\Models\Crm\CustomerTelephone;
use Common\Models\Crm\CustomerEmail;
use Common\Models\Crm\CustomerPaper;
use Common\Models\Crm\CustomerStatus;
use Common\Models\Crm\MarketingPhoneReport;

class CustomerServer extends BaseService {

    public function getCrmCustomer(User $user) {
        $birthday = $user->userInfo ? $user->userInfo->birthday : null;
        $birthday = $birthday ? date('Y-m-d', strtotime(str_replace('/', '-', $user->userInfo->birthday))) : null;

        $cardType = $user->card_type;
        $idCardNo = $user->id_card_no;

        $telephone = $user->telephone;
        $fullname = $user->fullname;

        $query = Customer::query();
        $isConditionEmpty = true;

        if ($telephone) {
            $query->orWhere(function (Builder $query) use ($telephone) {
                $query->where('telephone', $telephone);
            });
            $isConditionEmpty = false;
        }
        // 1、证件类型+证件号
        if ($cardType && $idCardNo) {
            $query->orWhere(function (Builder $query) use ($cardType, $idCardNo) {
                $query->where('id_type', $cardType)
                        ->where('id_number', $idCardNo);
            });
            $isConditionEmpty = false;
        }

        // 2、手机号+姓名
        if ($telephone && $fullname) {
            $query->orWhere(function (Builder $query) use ($telephone, $fullname) {
                $query->where('telephone', $telephone)
                        ->where('fullname', $fullname);
            });
            $isConditionEmpty = false;
        }

        // 3、姓名+出生日期
        if ($fullname && $birthday) {
            $query->orWhere(function (Builder $query) use ($fullname, $birthday) {
                $query->where('fullname', $fullname)
                        ->where('birthday', $birthday);
            });
            $isConditionEmpty = false;
        }

        if ($isConditionEmpty) {
            //throw new \Exception('用户不满足匹配条件');
        }
        if ($query->exists() && !$isConditionEmpty) {
            $customer = $query->first();
            $this->updateCustomer($customer, $user);
            $this->updateUserMap($customer, $user);
            return $this->setSettleInfo($customer);
        }
        $customer = $this->addCustomer($user);
        $customer = $this->setSettleInfo($customer);
        $this->updateUserMap($customer, $user);
        return $customer;
    }

    public function updateCustomer(Customer $customer, User $user) {
        $customerStatus = CustomerStatus::model()->updateOrCreateModel([
            "main_user_id" => $user->id,
            "customer_id" => $customer->id,
            'merchant_id' => $user->merchant_id,
                ], [
            "customer_id" => $customer->id,
            'merchant_id' => $user->merchant_id,
        ]);
        $customerStatus = CustomerStatus::model()->getStatus($customer->id, $user->merchant_id);
        $birthday = $user->userInfo ? $user->userInfo->birthday : null;
        $birthday = $birthday ? date('Y-m-d', strtotime(str_replace('/', '-', $user->userInfo->birthday))) : null;
        $email = $user->userInfo ? $user->userInfo->email : null;
        $gender = $user->userInfo ? $user->userInfo->gender : null;

        $cardType = $user->card_type;
        $idCardNo = $user->id_card_no;

        $telephone = $user->telephone;

//        print_r([$birthday, $email, $cardType, $idCardNo, $telephone]);
        try {
            $clmCustomer = ClmCustomerServer::server()->getCustomer($user);
        } catch (\Exception $e) {
            echo "CLM" . $e->getMessage() . PHP_EOL;
        }
//        $type = 3;

        $fullname = $user->fullname;
        $customer->main_user_id = $user->id;
        $customer->telephone = $telephone;
        $customer->telephone_status = $this->getTelephoneStatus($telephone);
        $customer->telephone_check_time = AirudderServer::server()->checkTime($telephone);
        if ($this->isFinishUser($user)) {
            $customerStatus->type = Customer::TYPE_RELOAN;
        }
        $customer->email = $email;
        $customer->gender = $gender;
        $customer->fullname = $fullname;
        $customer->birthday = $birthday;
        $customer->id_type = $cardType;
        $customer->id_number = $idCardNo;


        $userStatus = $this->getStatus($user);
        $customer->clm_level = $customerStatus->clm_level = isset($clmCustomer) ? $clmCustomer->getLevel() : null;
        $customerStatus->status_updated_time = $customer->getStatusLastTime();
        $customerStatus->status_stop_days = $customer->getStatusStopDays();

        $customerStatus->status = $userStatus;
        $customerStatus->last_login_time = $this->getLastLoginTime($user->id);
        $customerStatus->max_overdue_days = $this->getMaxOverdueDays($user->id);
        $customerStatus->save();
        $customer->save();
    }

    public function isFinishUser(User $user) {
        $res = Order::model()->where("user_id", $user->id)->whereIn('status', Order::FINISH_STATUS)->exists();
        return $res;
    }

    public function getMaxOverdueDays($userId) {
        $res = \Admin\Models\Order\RepaymentPlan::model()->newQuery()->where("user_id", $userId)->max("overdue_days");
        return $res ?: 0;
    }

    public function getLastLoginTime($userId) {
        $res = UserLoginLog::model()->newQuery()->where("user_id", $userId)->max("created_at");
        return $res ?: NULL;
    }

    public function getStatus(User $user) {
//            "1": "未注册",
//            "2": "注册未申请",
//            "3": "审批中",
//            "4": "审批拒绝",
//            "5": "放款处理中",
//            "6": "待还款",
//            "7": "逾期",
//            "8": "结清"
        $map = [
            Customer::STATUS_APPROVAL_PROGRESS => array_merge(Order::APPROVAL_PENDING_STATUS, [Order::STATUS_SYSTEM_APPROVING]),
            Customer::STATUS_APPROVAL_REJECT => Order::APPROVAL_REJECT_STATUS,
            Customer::STATUS_LENDING => [Order::STATUS_SIGN, Order::STATUS_PAYING, Order::STATUS_SYSTEM_PASS, Order::STATUS_MANUAL_PASS],
            Customer::STATUS_PAID => [Order::STATUS_SYSTEM_PAID, Order::STATUS_MANUAL_PAID],
            Customer::STATUS_OVERDUE => [Order::STATUS_OVERDUE],
            Customer::STATUS_FINISH => Order::FINISH_STATUS,
            Customer::STATUS_CANCEL => [Order::STATUS_SYSTEM_CANCEL, Order::STATUS_MANUAL_CANCEL, Order::STATUS_USER_CANCEL],
        ];
        if ($order = $user->order) {
            foreach ($map as $key => $val) {
                if (in_array($order->status, $val)) {
                    return $key;
                }
            }
        }
        if ($user->fullname != "") {
            return Customer::STATUS_APPLYING;
        }
        return Customer::STATUS_NOT_APPLY;
    }

    public function addCustomer(User $user) {
        $birthday = $user->userInfo ? $user->userInfo->birthday : null;
        $birthday = $birthday ? date('Y-m-d', strtotime(str_replace('/', '-', $user->userInfo->birthday))) : null;
        $email = $user->userInfo ? $user->userInfo->email : null;

        $cardType = $user->card_type;
        $idCardNo = $user->id_card_no;

        $telephone = $user->telephone;

        try {
            $clmCustomer = ClmCustomerServer::server()->getCustomer($user);
        } catch (\Exception $e) {
            //echo "clm" . $e->getMessage() . PHP_EOL;
        }

        $type = CustomerStatus::TYPE_GENERAL;
        if ($this->isFinishUser($user)) {
            $type = CustomerStatus::TYPE_RELOAN;
        }

        $fullname = $user->fullname;
        $attributes = [
            "telephone" => $telephone,
            "telephone_status" => $this->getTelephoneStatus($telephone),
            "telephone_check_time" => AirudderServer::server()->checkTime($telephone),
            "email" => $email,
            "fullname" => $fullname,
            "birthday" => $birthday,
            "id_type" => $cardType,
            "id_number" => $idCardNo,
        ];
        $customer = Customer::model()->createModel($attributes);
        CustomerStatus::model()->updateOrCreateModel([
            'merchant_id' => $user->merchant_id,
            "customer_id" => $customer->id,
            'type' => $type,
            "main_user_id" => $user->id,
            "clm_level" => isset($clmCustomer) ? $clmCustomer->getLevel() : null,
            "status" => $this->getStatus($user),
            "status_stop_days" => 1,
                ], [
            'merchant_id' => $user->merchant_id,
            "customer_id" => $customer->id,
        ]);
        return $customer;
    }

    public function setSettleInfo(Customer $customer) {
        $customerStatus = CustomerStatus::model()->newQuery()->where("customer_id", $customer->id)->get();
        foreach ($customerStatus as $status) {
            $sql = "SELECT count(1) as settle_count, max(updated_at) as last_settle_time from `order`
WHERE user_id='" . $status->main_user_id . "' AND `status` in('" . Order::STATUS_FINISH . "', '" . Order::STATUS_OVERDUE_FINISH . "')";
            $res = \DB::select($sql);
            $status->settle_times = $res[0]->settle_count;
            if ($status->settle_times == 0 && $status->type == CustomerStatus::TYPE_RELOAN) {
                $status->type = CustomerStatus::TYPE_GENERAL;
            }
            $status->last_settle_time = $res[0]->last_settle_time;
            $status->save();
        }
        return $customer;
    }

    public function getTelephoneStatus($telephone) {
        $telephoneStatus = AirudderServer::server()->check($telephone);
        if ($telephoneStatus == null) {
            $telephoneStatus = Customer::STATUS_TELEPHONE_NOT_DETECTED;
        } elseif ($telephoneStatus) {
            $telephoneStatus = Customer::STATUS_TELEPHONE_NORMAL;
        } else {
            $telephoneStatus = Customer::STATUS_TELEPHONE_FORGET;
        }
        return $telephoneStatus;
    }

    public function updateUserMap(Customer $customer, User $user) {
        $sql = "select o.user_id,d.name_cn,o.id from `order` o
INNER JOIN order_detail od ON o.id=od.order_id AND od.`key`='can_contact_time'
INNER JOIN dictionary d ON od.`value`=d.`code`
WHERE o.user_id={$user->id}
ORDER BY o.id DESC LIMIT 1";
        $res = \DB::select($sql);
        if ($res) {
            CustomerStatus::model()->updateOrCreateModel([
                "suggest_time" => $res[0]->name_cn
                    ], [
                "customer_id" => $customer->id,
                'merchant_id' => $user->merchant_id,
            ]);
        }
        $userMap = [
            "customer_id" => $customer->id,
            "user_id" => $user->id
        ];
        CustomerUserMap::model()->updateOrCreateModel($userMap, $userMap);
        CustomerTelephone::model()->updateOrCreateModel([
            "customer_id" => $customer->id,
            "telephone" => $user->telephone,
            "telephone_status" => $this->getTelephoneStatus($user->telephone),
            "telephone_test_time" => AirudderServer::server()->checkTime($user->telephone),
            "user_id" => $user->id
                ], [
            "customer_id" => $customer->id,
            "user_id" => $user->id
        ]);
        $email = $user->userInfo ? $user->userInfo->email : null;
        if ($email) {
            CustomerEmail::model()->updateOrCreateModel([
                "customer_id" => $customer->id,
                "user_id" => $user->id,
                "email" => $email,
                    ], [
                "customer_id" => $customer->id,
                "user_id" => $user->id
            ]);
        }
        $cardType = $user->card_type;
        $idCardNo = $user->id_card_no;
        if ($idCardNo) {
            CustomerPaper::model()->updateOrCreateModel([
                "customer_id" => $customer->id,
                "user_id" => $user->id,
                "id_type" => $cardType,
                "id_number" => $idCardNo,
                    ], [
                "customer_id" => $customer->id,
                "user_id" => $user->id
            ]);
        }
    }

    public function todayFinish($adminId) {
        $date = date("Y-m-d");
        $sql = "select count(1) as num from crm_marketing_phone_assign ass
INNER JOIN crm_customer_status cut ON ass.customer_id=cut.customer_id
INNER JOIN `order` o ON o.user_id=cut.main_user_id
WHERE o.signed_time>'{$date}' AND ass.saler_id='{$adminId}' AND date(ass.last_call_time)=date(o.signed_time)";
        $res = \DB::select($sql);
        $data['income'] = $res[0]->num;
        $data['coverage'] = \Common\Models\Crm\MarketingPhoneAssign::model()->where(\DB::raw("date(last_call_time)"), $date)->where("saler_id", $adminId)->get()->count();
        return $data;
    }

}
