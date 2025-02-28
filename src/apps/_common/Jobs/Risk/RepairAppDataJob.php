<?php

namespace Common\Jobs\Risk;

use Common\Jobs\Job;

class RepairAppDataJob extends Job
{
    public $queue = 'repair-app-data';

    public $tries = 3;

    protected $orderId;
    protected $userId;

    public function __construct($orderId, $userId)
    {
        $this->orderId = $orderId;
        $this->userId = $userId;
    }

    /**
     * @return bool
     */
    public function handle()
    {
        echo $sql = "UPDATE user_application SET order_id='{$this->orderId}' WHERE created_at>date(now()) AND user_id='{$this->userId}' AND 0 = (select count(1) from (SELECT * FROM user_application where order_id='{$this->orderId}') as a)";
        $res = \DB::update($sql);
        print_r($res);
        echo "END";
    }

}
