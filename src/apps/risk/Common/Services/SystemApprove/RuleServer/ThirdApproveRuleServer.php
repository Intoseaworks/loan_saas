<?php

namespace Risk\Common\Services\SystemApprove\RuleServer;

use Risk\Common\Services\SystemApprove\SystemApproveRuleServer;
use Common\Utils\Riskcloud\RiskcloudHelper;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Models\Business\User\UserBlacklist;

class ThirdApproveRuleServer {

    private $_riskCloudHelper;

    public function check(Order $order) {
        $this->_riskCloudHelper = new RiskcloudHelper();
        $resP = $this->verifyPancard($order->user_id, $order->user->userInfo->pan_card_no);
        if ($resP->isSuccess()) {
            $insert = [
                "type" => "panCard",
                "value" => $order->user->userInfo->pan_card_no,
                "from" => "Riskcloud"
            ];
            UserBlacklist::model()->createModel($insert);
            return ["HIT_TYPE" => "panCard", "HIT_VALUE" => $insert['value']];
        }

        $resT = $this->verifyTelephone($order->user_id, $order->user->telephone);
        if ($resT->isSuccess()) {
            //命中手机黑名单
            $insert = [
                "type" => "telephone",
                "value" => $order->user->telephone,
                "from" => "Riskcloud"
            ];
            UserBlacklist::model()->createModel($insert);
            return ["HIT_TYPE" => "telephone", "HIT_VALUE" => $insert['value']];
        }
        return false;
    }

    public function verifyTelephone($uid, $telephone) {
        $rData = [
            "uid" => $uid,
            "riskItem" => "MOBILE_NUMBER",
            "riskValue" => $telephone
        ];
        return $this->_riskCloudHelper->searchBlack($rData);
    }

    public function verifyPancard($uid, $pancard) {
        $rData = [
            "uid" => $uid,
            "riskItem" => "PAN_CARD_NUMBER",
            "riskValue" => $pancard
        ];
        return $this->_riskCloudHelper->searchBlack($rData);
    }

}
