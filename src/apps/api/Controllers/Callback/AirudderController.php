<?php

namespace Api\Controllers\Callback;

use Api\Models\User\UserContact;
use Common\Response\ServicesApiBaseController;
use Risk\Common\Models\Third\ThirdDataAirudder;
use Illuminate\Support\Facades\DB;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class AirudderController extends ServicesApiBaseController {

    public function webhook() {
        $params = $this->getParams();

//        Log::info(json_encode($params));
        //$content = json_decode('{"param":{"orderId":"5f33a6d97e818","phoneInfos":[{"gender":3,"business":2,"mobile":"8613008261034","wa":2}],"platformId":"853ad610e981419d9632c32b23b308cd"},"header":{"pragma":["no-cache"],"cache-control":["no-cache"],"content-type":["application\/json;charset=UTF-8"],"timestamp":["1597220616588"],"accept-language":["zh-CN,zh;q=0.8"],"accept-encoding":["gzip, deflate"],"sign":["E8D95C6A22FFA448132FDEF644F9A156"],"user-agent":["Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.142 Safari\/537.36 Hutool"],"accept":["text\/html,application\/json,application\/xhtml+xml,application\/xml;q=0.9,*\/*;q=0.8"],"content-length":["148"],"connection":["close"],"x-forwarded-for":["47.74.253.208"],"host":["120.78.230.66"],"x-real-ip":["47.74.253.208"]}}', true);
        if (isset($params['business'])) {
            $telephone = $params['business']['callee'];
            $status = $params['business']['numstatus'];
            $attributes = [
                "telephone" => $telephone,
                "webhook_data" => json_encode($params),
                "status" => $status,
                "updated_at" => date("Y-m-d H:i:s")
            ];
            ThirdDataAirudder::model()->updateOrCreateModel($attributes, ['order_id' => $params['business']['prvdata']]);
            $contactTelephone = substr($telephone, -10);
            /** 更新到UserContact */
            //UserContact::where("contact_telephone", "LIKE", "%{$contactTelephone}")->update(['check_status' => $status]);
            DB::update("UPDATE user_contact SET check_status='{$status}' WHERE (check_status='' OR check_status is null) AND contact_telephone_10P = '$contactTelephone';");
        }
        echo '{"code" : 200,"msg" : "ok"}';
        exit;
    }

}
