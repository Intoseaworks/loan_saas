<?php

namespace Api\Controllers\Columbia;

use Common\Response\ServicesApiBaseController;
use Common\Models\Columbia\ColUserCheck;

class UserCheckController extends ServicesApiBaseController {

    public function checkStatus() {
        $user = $this->identity();
        if (ColUserCheck::model()->where("user_id", $user->id)->where("status", "1")->exists()) {
            return $this->resultSuccess(["need_verify" => false, "residual_verify_count" => 0, "card_type" => $user->card_type]);
        }
        $verifyCount = ColUserCheck::model()->where("user_id", $user->id)->where("status", "2")->count();
        if ($verifyCount <= 3) {
            return $this->resultSuccess([
                "need_verify" => true,
                "residual_verify_count" => 3-$verifyCount>=0 ? 3-$verifyCount : 0,
                "card_type" => $user->card_type,
                "id_card_no" => \Common\Utils\Data\StringHelper::desensitization($user->id_card_no,2,strlen($user->id_card_no)-4)
                ]);
        }
        return $this->resultFail("You have failed the check more than 3 times, login will be blocked");
    }

    public function verify() {
        $user = $this->identity();
        $verifyCount = ColUserCheck::model()->where("user_id", $user->id);
        $userCheck = ColUserCheck::model()->createModel([
            "user_id" => $user->id,
            "check_card_type" => $this->getParam('card_type'),
            "check_card_id" => $this->getParam('id_card_no'),
        ]);
        if ($userCheck) {
            if ($user->card_type == $this->getParam('card_type') && $user->id_card_no == $this->getParam('id_card_no')) {
                $userCheck->status = 1;
            } else {
                $userCheck->status = 2;
            }
            $userCheck->save();
            return $this->resultSuccess(["verify_status" => $userCheck->status == 1]);
        } else {
            return $this->resultFail("System error");
        }
    }

}
