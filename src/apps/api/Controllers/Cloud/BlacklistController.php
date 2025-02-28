<?php

namespace Api\Controllers\Cloud;


use Common\Response\ServicesApiBaseController;
use Common\Models\Risk\RiskBlacklist;
use Common\Models\Cloud\ApiAccessLog;

class BlacklistController extends ServicesApiBaseController {


    public function check() {
        $params = $this->getParams();
        $res = $this->sign($params);
        $apiUserId = 0;
        if (is_numeric($res)) {
            $apiUserId = $res;
            $data = RiskBlacklist::model()->hitBlackList($params);
            $res = $this->resultSuccess($data);
        }
        ApiAccessLog::model()->createModel(
                [
                    "api_name" => "BLACKLIST",
                    "api_user_id" => $apiUserId,
                    "request_data" => json_encode($params),
                    "response_data" => json_encode($res),
                    "request_ip" => $this->_ip,
        ]);
        return $res;
    }

}
