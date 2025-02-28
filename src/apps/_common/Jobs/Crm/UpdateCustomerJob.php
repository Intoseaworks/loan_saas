<?php

namespace Common\Jobs\Crm;

use Common\Jobs\Job;
use Common\Services\Crm\CustomerServer;
use Common\Models\User\User;

class UpdateCustomerJob extends Job {

    public $queue = 'crm-customer-update-new-10';
    public $tries = 3;
    public $user;
    public $callTime;

    public function __construct($user) {
        $this->user = $user;
        $this->callTime = date("Y-m-d H:i:s");
    }

    public function handle() {
        echo $this->callTime."=>".$this->user->id . " start";
        if ($this->user) {
            CustomerServer::server()->getCrmCustomer($this->user);
        }
    }

}
