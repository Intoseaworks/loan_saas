<?php

namespace Risk\Common\Services\Risk;

use Common\Models\Order\Order;
use Common\Services\BaseService;
use Illuminate\Support\Facades\DB;

class RiskOrderServer extends BaseService {

    public function checkAadhaarCardOrder($aadhaarCardNo) {
        $orderApprovePass = "'" . implode("','", Order::STATUS_APPROVE_PASS) . "'";
        $sql = "select o.* from user_info ui 
INNER JOIN `user` u ON u.id=ui.user_id
INNER JOIN `order` o ON o.user_id=u.id AND o.`status` IN(" . $orderApprovePass . ")
WHERE ui.aadhaar_card_no='{$aadhaarCardNo}'";
        $res = DB::select($sql);
        if($res){
            return optional($res[0])->order_no;
        }
        return false;
    }

    public function checkPanCardOrder($panCard) {
        $orderApprovePass = "'" . implode("','", Order::STATUS_APPROVE_PASS) . "'";
        $sql = "select o.* from user_info ui 
INNER JOIN `user` u ON u.id=ui.user_id
INNER JOIN `order` o ON o.user_id=u.id AND o.`status` IN(" . $orderApprovePass . ")
WHERE ui.pan_card_no='{$panCard}'";
        $res = DB::select($sql);
        if($res){
            return optional($res[0])->order_no;
        }
        return false;
    }

    public function checkTelephoneOrder($telephone) {
        $sql = "SELECT o.* FROM `user` u
INNER JOIN `order` o ON o.user_id=u.id AND o.`status` IN('paying','system_paid','manual_paid','system_pay_fail','manual_pay_fail','overdue','repaying','collection_bad','sign','manual_pass','system_pass')
WHERE u.telephone='{$telephone}'";
        $res = DB::select($sql);
        if($res){
            return optional($res[0])->order_no;
        }
        return false;
    }

    public function checkBankcardOrder($bankCardNo) {
        $sql = "SELECT o.* FROM bank_card bc
INNER JOIN `user` u ON bc.user_id=u.id AND bc.`status`=1
INNER JOIN `order` o ON o.user_id=u.id AND o.`status` IN('paying','system_paid','manual_paid','system_pay_fail','manual_pay_fail','overdue','repaying','collection_bad','sign','manual_pass','system_pass')
WHERE bc.`no`='{$bankCardNo}'";
        $res = DB::select($sql);
        if($res){
            return optional($res[0])->order_no;
        }
        return false;
        
    }

}
