<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Common\Services\Collection;

use Common\Models\Collection\CollectionAssignJob;
use Common\Jobs\Collection\CollectionAssignJob as Job;
use Common\Services\BaseService;
use Common\Utils\MerchantHelper;

class CollectionAssignJobServer extends BaseService {

    public function assign($level, $ptp) {
        $merchantId = MerchantHelper::helper()->getMerchantId();
        if ($job = CollectionAssignJob::model()
                ->where("level_name", $level)
                ->where("ptp", $ptp ? 1 : 0)
                ->first()) {
            if (in_array($job->status, [CollectionAssignJob::STATUS_WAIT])) {
                return $this->outputSuccess("Queue waiting");
            }
            if (in_array($job->status, [CollectionAssignJob::STATUS_EXECUTING])) {
                return $this->outputSuccess("Queue executing");
            }
        }
        $res = CollectionAssignJob::model()->createModel([
            "level_name" => $level,
            "ptp" => $ptp ? 1 : 0,
            "status" => CollectionAssignJob::STATUS_WAIT,
            "merchant_id" => $merchantId
        ]);
        if ($res) {
            dispatch(new Job($res->id));
            return $this->outputSuccess();
        }
        return $this->outputError("Failed to create Job");
    }

}
