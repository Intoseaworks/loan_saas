<?php

namespace Api\Controllers\Cloud;

use Common\Models\Cloud\ApiAccessLog;
use Common\Response\ServicesApiBaseController;
use Common\Utils\Sms\SmsPesoLocalGsmHelper;

class SmsController extends ServicesApiBaseController {

    private $_ip = '1.1.1.1';

    public function send() {
        $params = $this->getParams();
        $res = $this->sign($params);
        if (is_numeric($res)) {
            $apiUserId = $res;
            $data = SmsPesoLocalGsmHelper::post($params['telephone'], $params['sms_content'], $params['sender_id'] ?? "UPESO");
            $res = $this->resultSuccess($data);
        }

        ApiAccessLog::model()->createModel(
                [
                    "api_name" => "SMS",
                    "api_user_id" => $apiUserId,
                    "request_data" => json_encode($params),
                    "response_data" => json_encode($res),
                    "request_ip" => $this->_ip,
        ]);
        return $res;
    }

}
