<?php

namespace Risk\Common\Jobs;

use Carbon\Carbon;
use Common\Utils\Data\StringHelper;
use Common\Utils\DingDing\DingHelper;
use Risk\Common\Models\Business\RiskData\RiskDataIndex;
use Risk\Common\Models\UserData\UserApp;
use Risk\Common\Models\UserData\UserApplicationDiffCreateInstall;
use Risk\Common\Services\NewRisk\RiskUserAppServer;

/**
 * 复贷变量线上化
 */
class ComputeReloanVarJob extends Job {

    public $queue = 'risk-default';

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 3;
    public $order;

    public function __construct($order) {
        $this->order = $order;
    }

    public function handle() {
        $server = new \Risk\Common\Services\Risk\RiskReloanVarOnlineServer($this->order);
        $res = $server->getAllVar();
        $server = new \Risk\Common\Services\Risk\RiskReloanAppVarOnlineServer($this->order);
        $res = array_merge($res,$server->getAllVar());
        print_r($res);
        foreach ($res as $key => $value) {
            $insertData = $relateData = [
                "user_id" => $this->order->user_id,
                "order_id" => $this->order->id,
                "index_name" => $key,
            ];
            $insertData['index_value'] = $value;
            RiskDataIndex::updateOrCreate($relateData, $insertData);
        }
    }

}
