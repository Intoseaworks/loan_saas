<?php

namespace Common\Jobs\Collection;

use Common\Console\Services\Collection\CollectionAssignServer;
use Common\Jobs\Job;
use Common\Utils\MerchantHelper;
use Common\Models\Collection\CollectionAssignJob as ModelJob;

class CollectionAssignJob extends Job {

    public $queue = 'collection_assign';
    public $tries = 0;
    public $jobId;

    public function __construct($jobId) {
        $this->jobId = $jobId;
    }

    public function handle() {
        $job = ModelJob::model()->getOne($this->jobId);
        if ($job) {
            echo "[{$job->id}]=>" . $job->level_name . PHP_EOL;
            $job->status = ModelJob::STATUS_EXECUTING;
            $job->save();
            $assignAgain = CollectionAssignServer::server();
            MerchantHelper::setMerchantId($job->merchant_id);
            $res = $assignAgain->manualAssignOrder($job->level_name, $job->ptp == 1 ? true : false, true);
            if ($res->isSuccess()) {
                $job->status = ModelJob::STATUS_SUCCESS;
            } else {
                $job->status = ModelJob::STATUS_FAILED;
                echo $res->getMsg();
            }
            $job->save();
        } else {
            echo "Id is {$this->jobId} does not exist";
        }
    }

}
