<?php

namespace Common\Jobs\Risk;

use Common\Jobs\Job;
use Common\Models\SystemApprove\SystemApproveTask;
use Common\Models\User\User;
use Common\Services\NewClm\ClmServer;

class MaxLevelUpdateJob extends Job
{
    public $queue = 'clm';

    public $tries = 2;

    protected $userId;

    public function __construct($userId, $type = null, SystemApproveTask $task = null)
    {
        $this->userId = $userId;
    }

    /**
     * @return bool
     */
    public function handle()
    {
        echo "更新MaxLevel ".$this->userId;
        $user = User::model()->getOne($this->userId);
        //容错
        if ($user){
            ClmServer::server()->updateMaxLevel($user);
        }
        echo " OVER";
    }
}
