<?php

namespace Risk\Common\Services\Risk;

use Common\Models\Order\Order;
use Common\Utils\Data\DateHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

class RiskReloanVarOnlineServer {

    CONST VAR_LIST = [
        'phone_brand',
        'ram_used',
        'cumpany_lth',
        'industry',
        'education_level',
        'working_time_type',
        'fst_rl',
        'province',
        'marital_status',
        'app_hour',
        'app_use_time',
        'loan_reason1',
        'pre_jq',
        'max_dpd_sgl_brand',
        'sb_last_eod'
    ];

    private $_order;

    public function __construct(Order $order) {
        $this->_order = $order;
        return $this;
    }

    public function getAllVar() {
        $res = [];
        $res['start_time'] = date("Y-m-d H:i:s");
        foreach (self::VAR_LIST as $item) {
            $functionName = Str::studly(Str::lower($item));
            if (!method_exists($this, $fun = "get{$functionName}")) {
                $res[$item] = 0;
            } else {
                $res[$item] = $this->$fun();
            }
        }
        $res['end_time'] = date("Y-m-d H:i:s");
        return $res;
    }

    public function getPhoneBrand() {
        $time = date("Y-m-d H:i:s", strtotime($this->_order->signed_time) + 60);
        $sql = "select phone_brand from user_phone_hardware where order_id={$this->_order->id} AND created_at<='{$time}' ORDER BY id DESC LIMIT 1";
        $res = DB::connection()->select($sql);
        return $res[0]->phone_brand ?? '';
    }

    public function getRamUsed() {
        $time = date("Y-m-d H:i:s", strtotime($this->_order->signed_time) + 61);
        $sql = "select used_ram as ram_used from user_phone_hardware where order_id={$this->_order->id} AND created_at<='{$time}' ORDER BY id DESC LIMIT 1";
        $res = DB::connection()->select($sql);
        if (isset($res[0])) {
            $usedRam = str_replace([','], '.', $res[0]->ram_used);
            if (stripos($usedRam, "GB") !== false) {
                $usedRam = floatval(str_replace(["GB", " "], "", $usedRam));
            }
            if (stripos($usedRam, "MB") !== false) {
                $usedRam = round(floatval(str_replace(["MB", " "], "", $usedRam)) / 1024, 2);
            }
            if (stripos($usedRam, "吉字节") !== false) {
                $usedRam = floatval(str_replace(["吉字节", " "], "", $usedRam));
            }
            return $usedRam;
        } else {
            return '';
        }
    }

    public function getCumpanyLth() {
        $sql = "select company from user_work where user_id='{$this->_order->user_id}'";
        $res = DB::connection()->select($sql);
        if (isset($res[0])) {
            return strlen(trim(str_replace(["  "], " ", $res[0]->company)));
        }
        return 0;
    }

    public function getIndustry() {
        $sql = "select industry from user_work where user_id='{$this->_order->user_id}'";
        $res = DB::connection()->select($sql);
        if (isset($res[0])) {
            return $res[0]->industry;
        }
        return 0;
    }

    public function getEducationLevel() {
        $sql = "select education_level from user_info where user_id='{$this->_order->user_id}'";
        $res = DB::connection()->select($sql);
        if (isset($res[0])) {
            return $res[0]->education_level;
        }
        return 0;
    }

    public function getWorkingTimeType() {
        $sql = "select working_time_type from user_work where user_id='{$this->_order->user_id}'";
        $res = DB::connection()->select($sql);
        if (isset($res[0])) {
            return $res[0]->working_time_type;
        }
        return 0;
    }

    public function getFstRl() {
        $sql = "select relation from user_contact where user_id='{$this->_order->user_id}' order by id limit 1";
        $res = DB::connection()->select($sql);
        if (isset($res[0])) {
            return $res[0]->relation;
        }
        return 0;
    }

    public function getProvince() {
        $sql = "select province from user_info where user_id='{$this->_order->user_id}'";
        $res = DB::connection()->select($sql);
        if (isset($res[0])) {
            return $res[0]->province;
        }
        return 0;
    }

    public function getMaritalStatus() {
        $sql = "select marital_status from user_info where user_id='{$this->_order->user_id}'";
        $res = DB::connection()->select($sql);
        if (isset($res[0])) {
            return $res[0]->marital_status;
        }
        return 0;
    }

    public function getAppHour() {
        return date("H", strtotime($this->_order->signed_time));
    }

    public function getAppUseTime() {
        return strtotime($this->_order->signed_time) - strtotime($this->_order->created_at);
    }

    public function getLoanReason1() {
        $sql = "select `value`, dict.`name` from order_detail od LEFT JOIN dictionary dict ON od.`value`=dict.`code` where order_id='{$this->_order->user_id}' AND `key`='loan_reason'";
        $res = DB::connection()->select($sql);
        if (isset($res[0])) {
            if ($res[0]->name) {
                return $res[0]->name;
            }
            return $res[0]->value;
        }
        return 0;
    }

    public function getPreJq() {
        $data = $this->getSameMerchant();
        $count = 0;
        $collect = [];
        foreach ($data as $item) {
            #获取o_paid_time日期 在本订单signed_time日期之前或等于
            if ($item->o2_paid_time <= $item->signed_time && $item->o2_repay_time <= $item->signed_time) {
                $collect[$item->o2_order_id] = $item;
            }
        }
        return count($collect);
    }

    public function getMaxDpdSglBrand() {
        $maxDays = 0;
        $data = $this->getSameMerchant();
        foreach ($data as $item) {
            # 选择其他品牌应还日在本订单的完件时间之前，且还款日期为空或还款日期在本订单完件时间之后的订单数据，
            if ($item->o2_appointment_paid_time < $item->signed_time && ($item->o2_repay_time == '' || $item->o2_repay_time > $item->signed_time)) {
                $diffDays = DateHelper::diffInDays($item->signed_time, $item->o2_appointment_paid_time);
                echo $item['o2_order_id'] . "=DIFF_DAYS=" . $diffDays . PHP_EOL;
                $maxDays = max($diffDays, $maxDays);
            }
        }
        return $maxDays;
    }

    public function getSbLastEod() {
        $sb_last_eod = $eod_days = 0;
        $max_o2_appointment_paid_time = '';
        $data = $this->getSameMerchant();
        foreach ($data as $item) {
            $o_appointment_paid_date = date("Y-m-d", strtotime($item->o2_appointment_paid_time));
            $signed_date = date("Y-m-d", strtotime($item->signed_time));
            $o_repay_date = date("Y-m-d", strtotime($item->o2_repay_time));

            if ($o_appointment_paid_date < $signed_date && $item->o2_repay_time == '') {
                $eod_days = DateHelper::diffInDays($signed_date, $o_appointment_paid_date);
            } elseif ($o_appointment_paid_date < $signed_date && $o_repay_date >= $signed_date && ($o_appointment_paid_date > '2020-06-15' || $o_repay_date < '2020-03-17')) {
                $eod_days = DateHelper::diffInDays($signed_date, $o_appointment_paid_date);
            } elseif ($o_appointment_paid_date < $signed_date && $o_repay_date >= $signed_date) {
                $eod_days = max(DateHelper::diffInDays($signed_date, '2020-06-15'), 0) + max(0, DateHelper::diffInDays('2020-03-17', $o_appointment_paid_date));
            } elseif ($o_appointment_paid_date < $signed_date && $o_repay_date >= $o_appointment_paid_date && ($o_appointment_paid_date > '2020-06-15' || $o_repay_date < '2020-03-17')) {
                $eod_days = DateHelper::diffInDays($o_repay_date, $o_appointment_paid_date);
            } elseif ($o_appointment_paid_date < $signed_date && $o_repay_date >= $o_appointment_paid_date) {
                $eod_days = max(DateHelper::diffInDays($o_repay_date, '2020-06-15'), 0) + max(0, DateHelper::diffInDays('2020-03-17', $o_appointment_paid_date));
            } elseif ($o_appointment_paid_date < $signed_date && $item->o2_repay_time != "" && $o_repay_date <= $o_appointment_paid_date) {
                $eod_days = DateHelper::diffInDays($o_repay_date, $o_appointment_paid_date);
            } elseif ($o_appointment_paid_date >= $signed_date && $item->o2_repay_time != "") {
                $eod_days = DateHelper::diffInDays($o_repay_date, $o_appointment_paid_date);
            }
            if($item->o2_appointment_paid_time == max($max_o2_appointment_paid_time, $item->o2_appointment_paid_time)){
                $max_o2_appointment_paid_time = $item->o2_appointment_paid_time;
                $sb_last_eod = $eod_days;
            }
        }
        return $sb_last_eod;
    }

    public function getBadApp() {

    }

    public function getGoodApp() {

    }

    public function getMaxIntLoanist() {

    }

    public function getMinIntAppist() {

    }

    public function getNsysLoanInM1() {

    }

    public function getStdCommonInstallDif() {

    }

    public function getDifCreateInstall() {

    }

    public function getSameMerchant() {
        $data = [];
        $sqlIdMatchO = "select o1.id as order_id,o1.signed_time,o2.merchant_id,o2.id as o2_order_id,o2.signed_time as o2_signed_time,o2.paid_time as o2_paid_time, rp2.repay_time as o2_repay_time,rp2.appointment_paid_time as o2_appointment_paid_time, 'o' as type
from `order` as o1
INNER JOIN `user` as u1 ON o1.user_id=u1.id
INNER JOIN `order` as o2 ON o1.merchant_id=o2.merchant_id AND o1.signed_time>o2.signed_time AND o2.signed_time is not null
INNER JOIN `user` as u2 ON o2.user_id=u2.id AND trim(lower(u1.id_card_no))=trim(lower(u2.id_card_no))
INNER JOIN repayment_plan rp2 ON o2.id=rp2.order_id AND rp2.repay_time is not null AND rp2.`installment_num`=1
WHERE o1.id='{$this->_order->id}'";
        $data = array_merge($data, DB::connection()->select($sqlIdMatchO));

        $sqlNbMatchO = "select o1.id as order_id,o1.signed_time,o2.merchant_id,o2.id as o2_order_id,o2.signed_time as o2_signed_time,o2.paid_time as o2_paid_time, rp2.repay_time as o2_repay_time,rp2.appointment_paid_time as o2_appointment_paid_time, 'o' as type
from `order` as o1
INNER JOIN `user` as u1 ON o1.user_id=u1.id
INNER JOIN user_info as ui1 ON u1.id=ui1.user_id
INNER JOIN `order` as o2 ON o1.merchant_id=o2.merchant_id AND o1.signed_time>o2.signed_time AND o2.signed_time is not null
INNER JOIN `user` as u2 ON o2.user_id=u2.id AND lower(replace(u1.fullname,' ',''))=lower(replace(u2.fullname,' ',''))
INNER JOIN user_info as ui2 ON u2.id=ui2.user_id AND SUBSTRING(ui1.birthday,-4)=SUBSTRING(ui2.birthday,-4)
INNER JOIN repayment_plan rp2 ON o2.id=rp2.order_id AND rp2.repay_time is not null AND rp2.`installment_num`=1
WHERE o1.id='{$this->_order->id}'";
        $data = array_merge($data, DB::connection()->select($sqlNbMatchO));

        $sqlPhMatchO = "select o1.id as order_id,o1.signed_time,o2.merchant_id,o2.id as o2_order_id,o2.signed_time as o2_signed_time,o2.paid_time as o2_paid_time, rp2.repay_time as o2_repay_time,rp2.appointment_paid_time as o2_appointment_paid_time, 'o' as type
from `order` as o1
INNER JOIN `user` as u1 ON o1.user_id=u1.id
INNER JOIN `order` as o2 ON o1.merchant_id=o2.merchant_id AND o1.signed_time>o2.signed_time AND o2.signed_time is not null
INNER JOIN `user` as u2 ON o2.user_id=u2.id AND u1.telephone=u2.telephone
INNER JOIN repayment_plan rp2 ON o2.id=rp2.order_id AND rp2.repay_time is not null AND rp2.`installment_num`=1
WHERE o1.id='{$this->_order->id}'";
        $data = array_merge($data, DB::connection()->select($sqlPhMatchO));

        $sqlEmMatchO = "select o1.id as order_id,o1.signed_time,o2.merchant_id,o2.id as o2_order_id,o2.signed_time as o2_signed_time,o2.paid_time as o2_paid_time, rp2.repay_time as o2_repay_time,rp2.appointment_paid_time as o2_appointment_paid_time, 'o' as type
from `order` as o1
INNER JOIN `user` as u1 ON o1.user_id=u1.id
INNER JOIN user_info as ui1 ON u1.id=ui1.user_id
INNER JOIN `order` as o2 ON o1.merchant_id=o2.merchant_id AND o1.signed_time>o2.signed_time AND o2.signed_time is not null
INNER JOIN `user` as u2 ON o2.user_id=u2.id
INNER JOIN user_info as ui2 ON u2.id=ui2.user_id AND TRIM(ui1.email)=TRIM(ui2.email) AND TRIM(ui1.email)<>'abc123@gmail.com'
INNER JOIN repayment_plan rp2 ON o2.id=rp2.order_id AND rp2.repay_time is not null AND rp2.`installment_num`=1
WHERE o1.id='{$this->_order->id}'";
        $data = array_merge($data, DB::connection()->select($sqlEmMatchO));
        return $data;
    }

    public function getDiffMerchant() {
        $data = [];
        $sqlIdMatchO = "select o1.id as order_id,o1.signed_time,o2.merchant_id,o2.id as o2_order_id,o2.signed_time as o2_signed_time,o2.paid_time as o2_paid_time, rp2.repay_time as o2_repay_time,rp2.appointment_paid_time as o2_appointment_paid_time, 'o' as type
from `order` as o1
INNER JOIN `user` as u1 ON o1.user_id=u1.id
INNER JOIN `order` as o2 ON o1.merchant_id<>o2.merchant_id AND o1.signed_time>o2.signed_time AND o2.signed_time is not null
INNER JOIN `user` as u2 ON o2.user_id=u2.id AND trim(lower(u1.id_card_no))=trim(lower(u2.id_card_no))
INNER JOIN repayment_plan rp2 ON o2.id=rp2.order_id AND rp2.repay_time is not null AND rp2.`installment_num`=1
WHERE o1.id='{$this->_order->id}'";
        $data = array_merge($data, DB::connection()->select($sqlIdMatchO));

        $sqlNbMatchO = "select o1.id as order_id,o1.signed_time,o2.merchant_id,o2.id as o2_order_id,o2.signed_time as o2_signed_time,o2.paid_time as o2_paid_time, rp2.repay_time as o2_repay_time,rp2.appointment_paid_time as o2_appointment_paid_time, 'o' as type
from `order` as o1
INNER JOIN `user` as u1 ON o1.user_id=u1.id
INNER JOIN user_info as ui1 ON u1.id=ui1.user_id
INNER JOIN `order` as o2 ON o1.merchant_id<>o2.merchant_id AND o1.signed_time>o2.signed_time AND o2.signed_time is not null
INNER JOIN `user` as u2 ON o2.user_id=u2.id AND lower(replace(u1.fullname,' ',''))=lower(replace(u2.fullname,' ',''))
INNER JOIN user_info as ui2 ON u2.id=ui2.user_id AND SUBSTRING(ui1.birthday,-4)=SUBSTRING(ui2.birthday,-4)
INNER JOIN repayment_plan rp2 ON o2.id=rp2.order_id AND rp2.repay_time is not null AND rp2.`installment_num`=1
WHERE o1.id='{$this->_order->id}'";
        $data = array_merge($data, DB::connection()->select($sqlNbMatchO));

        $sqlPhMatchO = "select o1.id as order_id,o1.signed_time,o2.merchant_id,o2.id as o2_order_id,o2.signed_time as o2_signed_time,o2.paid_time as o2_paid_time, rp2.repay_time as o2_repay_time,rp2.appointment_paid_time as o2_appointment_paid_time, 'o' as type
from `order` as o1
INNER JOIN `user` as u1 ON o1.user_id=u1.id
INNER JOIN `order` as o2 ON o1.merchant_id<>o2.merchant_id AND o1.signed_time>o2.signed_time AND o2.signed_time is not null
INNER JOIN `user` as u2 ON o2.user_id=u2.id AND u1.telephone=u2.telephone
INNER JOIN repayment_plan rp2 ON o2.id=rp2.order_id AND rp2.repay_time is not null AND rp2.`installment_num`=1
WHERE o1.id='{$this->_order->id}'";
        $data = array_merge($data, DB::connection()->select($sqlPhMatchO));

        $sqlEmMatchO = "select o1.id as order_id,o1.signed_time,o2.merchant_id,o2.id as o2_order_id,o2.signed_time as o2_signed_time,o2.paid_time as o2_paid_time, rp2.repay_time as o2_repay_time,rp2.appointment_paid_time as o2_appointment_paid_time, 'o' as type
from `order` as o1
INNER JOIN `user` as u1 ON o1.user_id=u1.id
INNER JOIN user_info as ui1 ON u1.id=ui1.user_id
INNER JOIN `order` as o2 ON o1.merchant_id<>o2.merchant_id AND o1.signed_time>o2.signed_time AND o2.signed_time is not null
INNER JOIN `user` as u2 ON o2.user_id=u2.id
INNER JOIN user_info as ui2 ON u2.id=ui2.user_id AND TRIM(ui1.email)=TRIM(ui2.email) AND TRIM(ui1.email)<>'abc123@gmail.com'
INNER JOIN repayment_plan rp2 ON o2.id=rp2.order_id AND rp2.repay_time is not null AND rp2.`installment_num`=1
WHERE o1.id='{$this->_order->id}'";
        $data = array_merge($data, DB::connection()->select($sqlEmMatchO));
        return $data;
    }

}
