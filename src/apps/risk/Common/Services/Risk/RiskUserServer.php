<?php

namespace Risk\Common\Services\Risk;

use Illuminate\Support\Facades\Cache;
use Common\Services\BaseService;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Models\Business\UserData\UserContactsTelephone;
use Risk\Common\Models\Business\UserData\UserPhoneHardware;
use Risk\Common\Models\Business\UserData\UserPhonePhoto;
use Illuminate\Support\Facades\DB;
use Risk\Common\Services\Risk\RiskUserAppServer;
use Risk\Common\Models\Business\RiskData\RiskDataIndex;

class RiskUserServer extends BaseService {

    const BASE_SCORE = 498;
    
    public function riskIndex20200906(Order $order) {
        $indexList = [];
        $indexList['dr_phone_num'] = $this->getDrPhoneNum($order);
        $indexList['last_net_type'] = $this->getLastNetType($order);
        $indexList['age'] = $this->getAge($order);
        $indexList['province'] = $this->getProvince($order);
        $indexList['work_time_year'] = $this->getWorkTimeYear($order);
        $indexList['phone_pre2'] = $this->getPhonePre2($order);
        $indexList['reg_hour'] = $this->getRegHour($order);
        $indexList['b30d_photo_cnt'] = $this->getB30dPhotoCnt($order);
        $indexList['fillinfo_time'] = $this->getFillinfoTime($order);
        $indexList['diftime_facetest'] = $this->getDiftimeFacetest($order);
        $indexList = array_merge(RiskUserAppServer::server()->riskIndex20200906($order), $indexList);
        foreach ($indexList as $k => $v) {
            $insertData = [
                "user_id" => $order->user_id,
                "order_id" => $order->id,
                "index_name" => $k,
                "index_value" => $v,
            ];
            $relateData = [
                "user_id" => $order->user_id,
                "order_id" => $order->id,
                "index_name" => $k,
            ];
            RiskDataIndex::updateOrCreate($relateData, $insertData);
        }
        return $indexList;
    }

    public function getDrPhoneNum(Order $order) {
        $query = UserContactsTelephone::model()->newQuery();
        $select = DB::raw("LOWER(contact_fullname) as fullname,SUBSTR(contact_telephone, -10) as telephone");
        $res = $query->where(['user_id' => $order->user_id])->groupBy(["fullname", "telephone"])->select($select)->get()->toArray();
        $drCnt = 0;
        foreach ($res as $item) {
            if (false !== strpos($item['fullname'], 'dr')) {
                $drCnt++;
            }
        }
        return $drCnt;
    }

    public function getLastNetType(Order $order) {
        $query = UserPhoneHardware::model()->newQuery();
        if($query->where(["user_id" => $order->user_id])->orderByDesc("id")->select("net_type_original")->first()){
        $res = $query->where(["user_id" => $order->user_id])->orderByDesc("id")->select("net_type_original")->first()->toArray();
        }
        return $res['net_type_original'] ?? "";
    }

    public function getAge(Order $order) {
        $birthDay = str_replace('/', '-', $order->user->userInfo->birthday);
        $y1 = date("Y", strtotime($birthDay));
        $y2 = date("Y", strtotime($order->created_at));
        return $y2-$y1;
    }

    public function getProvince(Order $order) {
        return strtoupper(str_replace(" ", "", $order->user->userInfo->province));
    }

    public function getWorkTimeYear(Order $order) {
        $startTime = strtotime(str_replace('/', '-', $order->user->userWork->work_start_date));
        $endTime = strtotime(substr($order->created_at,0,10)." 00:00:00");
        return (($endTime - $startTime) / 3600 / 24) / 360;
    }

    public function getPhonePre2(Order $order) {
        return substr($order->user->telephone, 0, 2);
    }

    public function getRegHour(Order $order) {
        return date("G", strtotime($order->user->created_at));
    }

    public function getB30dPhotoCnt(Order $order) {
        $query = UserPhonePhoto::model()->newQuery()->whereRaw("TO_DAYS(created_at)-TO_DAYS(photo_created_time)>30 AND user_id={$order->user_id}")->where("created_at", "<=", $order->created_at);
        return count($query->groupBy("storage")->select("storage")->get());
    }

    public function getFillinfoTime(Order $order) {
        $sql = "select `name`,min(created_at) min_created,max(created_at) max_created from action_log 
WHERE `name`IN ('open_basic_detail','click_basic_detail_next','open_emergent_contacts','click_emergent_contacts_step',
'open_face_recognition','submit_authentication_center','open_ekyc_auth_page','done_ekyc_verify_success',
'open_pan_card_page','click_pan_card_next_step','open_personal_info&address','click_personal_info&address_continue',
'click_add_bank_account_details_page','click_bank_account_confirm_and_proceed')
AND user_id={$order->user_id}
AND created_at<='{$order->created_at}'
GROUP BY `name`";
        $res = DB::select($sql);
        $startTime = '';
        $endTime = '';
        foreach ($res as $item) {
            if ($item->name == 'open_basic_detail') {
                $startTime = $item->min_created;
            }
            if ($item->min_created > $endTime) {
                $endTime = $item->min_created;
            }
        }
        //echo "{$startTime} -> {$endTime}";
        return strtotime($endTime) - strtotime($startTime);
    }

    public function getDiftimeFacetest(Order $order) {
        $sql = "select `name`,min(created_at) min_created,max(created_at) max_created from action_log 
WHERE `name`IN ('open_basic_detail','click_basic_detail_next','open_emergent_contacts','click_emergent_contacts_step',
'open_face_recognition','submit_authentication_center','open_ekyc_auth_page','done_ekyc_verify_success',
'open_pan_card_page','click_pan_card_next_step','open_personal_info&address','click_personal_info&address_continue',
'click_add_bank_account_details_page','click_bank_account_confirm_and_proceed')
AND user_id={$order->user_id}
AND created_at<='{$order->created_at}'
GROUP BY `name`";
        $res = DB::select($sql);
        $startTime = '';
        $endTime = '';
        foreach ($res as $item) {

            if ($item->name == 'open_face_recognition') {
                $startTime = $item->max_created;
            }
            if ($item->name == 'submit_authentication_center') {
                $endTime = $item->max_created;
            }
        }
        return strtotime($endTime) - strtotime($startTime);
    }

    public function getScore(Order $order) {
        $score = self::BASE_SCORE;
        $indexData = $this->riskIndex20200906($order);
        if ($indexData['last_net_type']) {
            if ($indexData['last_net_type'] == "CONNECTED") {
                $score -= 1;
            } else {
                $score += 7;
            }
        } else {
            $score += 7;
        }

        if (in_array($indexData['phone_pre2'], ["92", "97", "81", "69", "94", "60"])) {
            $score += 7;
        } elseif (in_array($indexData['phone_pre2'], ["93", "98", "99", "82", "90", "76", "89", "73", "70"])) {
            $score += 3;
        } elseif (in_array($indexData['phone_pre2'], ["92", "97", "81", "69", "94", "60"])) {
            $score += 7;
        } elseif (in_array($indexData['phone_pre2'], ["95", "96", "77"])) {
            $score += 0;
        } else {
            $score -= 7;
        }

        if ("" == $indexData['province'] || in_array($indexData['province'], ["ANDHRAPRADESH", "MIZORAM", "CHANDIGARH", "TRIPURA", "S", "MANIPUR", "HARYANA", "HARYANA", "SIKKIM", "JAMMU&KASHMIR", "KERALA", "DADRA&NAGARHAVELI", "HIMACHALPRADESH", "MEGHALAYA", "PONDICHERRY", "PUNJAB", "CHHATTISGARH", "NAGALAND", "UTTARAKHAND", "ANDHRAPRADESH", "ARUNACHALPRADESH", "KERALA", "CHATTISGARH", "GOA"])) {
            $score += 7;
        } elseif (in_array($indexData['province'], ["TELANGANA", "GUJARAT", "GUJARAT", "TELANGANA"])) {
            $score += 3;
        } else {
            $score -= 3;
        }

        if ($indexData['diftime_facetest'] > 0 && $indexData['diftime_facetest'] <= 16) {
            $score += 2;
        } elseif ($indexData['diftime_facetest'] > 16) {
            $score -= 4;
        } else {
            $score -= 4;
        }

        if ($indexData['fillinfo_time'] > 0 && $indexData['fillinfo_time'] <= 432) {
            $score += 6;
        } elseif ($indexData['fillinfo_time'] > 432 && $indexData['fillinfo_time'] <= 41246) {
            $score += 0;
        } elseif ($indexData['fillinfo_time'] > 41246) {
            $score -= 8;
        }

        if ($indexData['last_pack_cnt'] > 0 && $indexData['last_pack_cnt'] <= 421) {
            $score -= 1;
        } elseif ($indexData['last_pack_cnt'] > 421) {
            $score += 8;
        } else {
            $score -= 1;
        }

        if ($indexData['last_install_loan_app1_b30d_rate'] > 0 && $indexData['last_install_loan_app1_b30d_rate'] <= 0.004778979) {
            $score -= 14;
        } elseif ($indexData['last_install_loan_app1_b30d_rate'] > 0.004778979) {
            $score += 6;
        } else {
            $score += 6;
        }

        if ($indexData['keep_loan_pack_rate'] > 0 && $indexData['keep_loan_pack_rate'] <= 0.070278905) {
            $score -= 4;
        } elseif ($indexData['keep_loan_pack_rate'] > 0.070278905) {
            $score += 9;
        } else {
            $score -= 4;
        }

        if ($indexData['dr_phone_num'] > 0 && $indexData['dr_phone_num'] <= 13) {
            $score -= 3;
        } elseif ($indexData['dr_phone_num'] > 13) {
            $score += 9;
        } else {
            $score -= 3;
        }

        if ($indexData['b30d_photo_cnt'] > 467) {
            $score += 14;
        } else {
            $score -= 1;
        }

        if ($indexData['reg_hour'] > 0 && $indexData['reg_hour'] <= 9) {
            $score -= 1;
        } elseif ($indexData['reg_hour'] > 9 && $indexData['reg_hour'] <= 12) {
            $score -= 2;
        } elseif ($indexData['reg_hour'] > 12 && $indexData['reg_hour'] <= 16) {
            $score -= 1;
        } elseif ($indexData['reg_hour'] > 16 && $indexData['reg_hour'] <= 19) {
            $score -= 2;
        } elseif ($indexData['reg_hour'] > 19 && $indexData['reg_hour'] <= 23) {
            $score += 10;
        }

        if ($indexData['age'] > 0 && $indexData['age'] <= 23) {
            $score -= 21;
        } elseif ($indexData['age'] > 23 && $indexData['age'] <= 32) {
            $score += 0;
        } elseif ($indexData['age'] > 32) {
            $score += 5;
        }

        if ($indexData['work_time_year'] > 0 && $indexData['work_time_year'] <= 2.134722222) {
            $score += 0;
        } elseif ($indexData['work_time_year'] > 2.134722222 && $indexData['work_time_year'] <= 7.581944445) {
            $score -= 1;
        } elseif ($indexData['work_time_year'] > 7.581944445) {
            $score += 4;
        }
        $insertData = [
            "user_id" => $order->user_id,
            "order_id" => $order->id,
            "index_name" => "user_score",
            "index_value" => $score,
        ];
        $relateData = [
            "user_id" => $order->user_id,
            "order_id" => $order->id,
            "index_name" => 'user_score',
        ];
        RiskDataIndex::updateOrCreate($relateData, $insertData);
        return $score;
    }

}
