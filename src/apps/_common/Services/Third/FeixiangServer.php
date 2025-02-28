<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/18
 * Time: 20:07
 */

namespace Common\Services\Third;

use Common\Models\Collection\CollectionThirdLog;
use Common\Models\Order\Order;
use Common\Models\Order\OrderDetail;
use Common\Models\User\UserContact;
use Common\Models\User\UserInfo;
use Common\Models\UserData\UserContactsTelephone;
use Common\Services\BaseService;
use Common\Utils\Data\DateHelper;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Feixiang\FeixiangApi;

class FeixiangServer extends BaseService
{

    public $appName = "Urupee"; //应用名称
    public $adminIds = [29]; //分给飞象的AdminIds
    private $adminId = "0";
    private $_pass = true; //是否分配，判断adminID是否在adminIds数组内

    const EDUCATED = [
        UserInfo::EDUCATIONAL_TYPE_PRIMARY => "1",
        UserInfo::EDUCATIONAL_TYPE_GENERAL_SECONDARY => "2",
        UserInfo::EDUCATIONAL_TYPE_SENIOR_SECONDARY => "4",
        UserInfo::EDUCATIONAL_TYPE_UNIVERSITY_DEGREE => "5",
        UserInfo::EDUCATIONAL_TYPE_POSTGRADUATE => "6",
        UserInfo::EDUCATIONAL_TYPE_DOCTORAL_DEGREE => "7",
        UserInfo::EDUCATIONAL_TYPE_DOCTORAL_OTHER => "0",
    ];
    const MARITAL = [
        UserInfo::MARITAL_UNMARRIED => 1,
        UserInfo::MARITAL_MARRIED => 2,
        UserInfo::MARITAL_DIVORCE => 4,
        UserInfo::MARITAL_WIDOWHOOD => 4,
    ];
    const RELATIVE = [
        UserContact::RELATION_FATHER => "1",
        UserContact::RELATION_MOTHER => "1",
        UserContact::RELATION_BROTHERS => "2",
        UserContact::RELATION_SISTERS => "3",
        UserContact::RELATION_SON => "4",
        UserContact::RELATION_DAUGHTER => "4",
        UserContact::RELATION_WIFE => "5",
        UserContact::RELATION_HUSBAND => "5",
        UserContact::RELATION_OTHER => "5",
    ];

    public function collectionOrderOverdue(Order $order) {
        //判断是否有分配飞象
        if (!$this->_pass) {
            return;
        }
        $data = [
            "user_id" => $order->user_id,
            "order_id" => $order->id,
            "app_name" => $this->appName,
            "total_money" => $order->repayAmount() * 100,
            "overdue_day" => $order->loan_days,
            "overdue_fee" => $order->getPenaltyFeeAddGst() * 100
        ];
        $fx = new FeixiangApi();
        $res = $fx->orderOverdue($data);
        return $res;
    }

    public function collectionOrderRepayment(Order $order, $requestId = "A", $money = 0) {
        //判断是否有分配飞象
        if (!$this->_pass) {
            return;
        }
        $data = [
            "user_id" => $order->user_id,
            "order_id" => $order->id,
            "app_name" => $this->appName,
            "request_id" => $requestId,
            "total_money" => $order->repayAmount() * 100,
            "true_total_money" => $order->getPartRepayAmount() * 100,
            "money" => $money * 100,
            "overdue_day" => $order->loan_days,
            "overdue_fee" => $order->getPenaltyFeeAddGst() * 100,
            "status" => in_array($order->status, Order::FINISH_STATUS) ? 1 : 0
        ];
        $fx = new FeixiangApi();
        $res = $fx->orderRepayment($data);
        return $res;
    }

    public function collectionUploadContact(Order $order, $num) {
        //判断是否有分配飞象
        if (!$this->_pass) {
            return;
        }
        $contacts = UserContactsTelephone::model()->getContacts($order->user_id, $num);
        $dataContacts = [];
        foreach ($contacts as $contact) {
            $dataContacts[] = [
                "name" => $contact->contact_fullname,
                "mobile" => $contact->contact_telephone
            ];
        }
        $data = [
            "user_id" => $order->user_id,
            "order_id" => $order->id,
            "app_name" => $this->appName,
            "data" => json_encode($dataContacts)
        ];
        $fx = new FeixiangApi();
        $res = $fx->uploadContact($data);
        return $res;
    }

    public function setAdminId($adminId) {
        $this->adminId = $adminId;
    }

    public function checkAdminId($adminId) {
        $this->_pass = in_array($adminId, $this->adminIds);
        if ($this->_pass) {
            $this->adminId = $adminId;
        }
        return $this;
    }

    public function isPass() {
        return $this->_pass;
    }

    public function collectionApply(Order $order) {
        //判断是否有分配飞象
        if (!$this->_pass) {
            return;
        }
        $userContact = $order->user->userContacts()->get()->toArray();
        $userInfo = [
            "name" => $order->user->fullname,
            "phone" => $order->user->telephone,
            "sex" => $order->userInfo->gender == "Male" ? 1 : 0,
            "birthday" => DateHelper::formatToDate($order->userInfo->birthday, 'Y-m-d'),
            "pan_code" => $order->userInfo->pan_card_no,
            "aadhaar" => $order->userInfo->aadhaar_card_no,
            "educated" => self::EDUCATED[$order->userInfo->education_level] ?? 0,
            "marital" => self::MARITAL[$order->userInfo->marital_status] ?? 0,
            "residential_address1" => $order->userInfo->province,
            "residential_address2" => $order->userInfo->city,
            "residential_detail_address" => $order->userInfo->address,
            "company_name" => $order->userInfo->userWork->company ?? "",
            "company_address" => $order->userInfo->userWork->company ?? "",
            "company_phone" => "",
            "product_name" => $this->appName,
            "source_from" => "Urupee",
            "aadhaar_address1" => $order->userInfo->permanent_province,
            "aadhaar_address2" => $order->userInfo->permanent_city,
            "aadhaar_detail_address" => $order->userInfo->permanent_address,
            "contact1_name" => $userContact[0]['contact_fullname'] ?? "",
            "contact1_mobile_number" => $userContact[0]['contact_telephone'] ?? "",
            "contact1_relative" => self::RELATIVE[$userContact[0]['relation']?? "OTHER"] ?? 5,
            "contact2_name" => $userContact[1]['contact_fullname'] ?? "",
            "contact2_mobile_number" => $userContact[1]['contact_telephone'] ?? "",
            "contact2_relative" => self::RELATIVE[$userContact[1]['relation']?? "OTHER"] ?? ""
        ];
        $order_info = [
            "status" => 0,
            "loan_time" => strtotime($order->paid_time),
            "loan_term" => $order->loan_days,
            "plan_repayment_time" => strtotime($order->getAppointmentPaidTime()),
            "true_total_money" => $order->getPartRepayAmount() * 100,
            "total_money" => $order->repayAmount() * 100,
            "principal" => $order->principal * 100,
            "interests" => $order->interestFee() * 100,
            "cost_fee" => $order->getProcessingFee() * 100,
            "overdue_fee" => $order->getPenaltyFeeAddGst() * 100,
            "coupon_money" => "0",
            "overdue_day" => $order->getOverdueDays() ?: 0,
            "is_first" => $order->user->quality,
            "repay_count" => $order->user->getRepeatLoanCnt(),
            "imei" => "",
        ];
        $data = [
            "user_id" => $order->user_id,
            "order_id" => $order->id,
            "user_basic_info" => $userInfo,
            "order_info" => $order_info,
            "app_name" => "Urupee"
        ];
        $fx = new FeixiangApi();
        $res = $fx->apply($data);
        if ("0" == $res['code']) {
            CollectionThirdLog::model()->createModel([
                "third_name" => "Feixiang",
                "order_id" => $order->id,
                "admin_id" => $this->adminId,
                "apply_data" => json_encode($data)
            ]);
        }
        return $res;
    }

    public function payoutNotify($order, $money, $success = true) {

        if ($order->app_client == "FX") {
            $order->refresh();
            $orderDetail = new OrderDetail();
            $data['orderID'] = $orderDetail->getValueByKey($order, "product_id");
            $data['panMd5'] = md5($order->userInfo->pan_card_no);
            $data['disbursalAmount'] = round($money) * 100;
            $data['loanTime'] = $order->paid_time ? strtotime($order->paid_time) : time();
            $data['isPayment'] = $success;


            $fx = new FeixiangApi();
            $res = $fx->lmPost($data, "/merchant/payout-notify");
            $server = LoanMarketServer::server();
            $log = $server->log("payout-notify", $data);
            $log->response_data = json_encode($res);
            $log->save();
            if ($res['code'] != "0") {
                DingHelper::notice(json_encode($res), "Fx放款回调错误订单【{$order->id}】", DingHelper::AT_SOLIANG);
                return false;
            }
            return true;
        }
        return false;
    }

    public function paymentNotify($order, $money, $paymentID) {

        if ($order->app_client == "FX") {
            $orderDetail = new OrderDetail();
            $data['orderID'] = $orderDetail->getValueByKey($order, "product_id");
            $data['panMd5'] = md5($order->userInfo->pan_card_no);
            $data['repaymentAmount'] = $money * 100;
            $data['successTime'] = time();
            $data['paymentID'] = $paymentID;
            $fx = new FeixiangApi();
            $res = $fx->lmPost($data, "/merchant/payment-notify");
            $server = LoanMarketServer::server();
            $log = $server->log("payment-notify", $data);
            $log->response_data = json_encode($res);
            $log->save();
            if ($res['code'] != "0") {
                DingHelper::notice(json_encode($res), "Fx还款回调错误订单【{$order->id}】");
                return false;
            }
        }
        return false;
    }

    public function closeOrder($order) {
        $orderDetail = new OrderDetail();
        $data['orderID'] = $orderDetail->getValueByKey($order, "product_id");
        if ($data['orderID']) {
            $data['panMd5'] = md5($order->userInfo->pan_card_no);
            $data['reason'] = "关闭订单";
            $data['closeTime'] = time();
            $fx = new FeixiangApi();
            $res = $fx->lmPost($data, "/merchant/order-force-close");
            $server = LoanMarketServer::server();
            $log = $server->log("order-force-close", $data);
            $log->response_data = json_encode($res);
            $log->save();
            if ($res['code'] != "0") {
                DingHelper::notice(json_encode($res), "Fx关闭订单【{$order->id}】错误");
                return false;
            }
        }
        return false;
    }

}
