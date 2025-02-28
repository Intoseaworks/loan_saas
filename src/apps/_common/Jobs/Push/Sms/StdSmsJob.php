<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/26
 * Time: 10:18
 */

namespace Common\Jobs\Push\Sms;

use Common\Jobs\Job;
use Common\Utils\Sms\SmsPesoHelper;

class StdSmsJob extends Job {
    
//    public $queue = "test-it";
    public $tries = 3;
    public $telephone;
    public $content;
    public $senderId;
    public $values = [];

    public function __construct($telephone, $content, $senderId, $values = []) {
        $this->telephone = $telephone;
        $this->content = $content;
        $this->senderId = $senderId;
        $this->values = $values;
    }

    public function handle() {
        echo "Tel:{$this->telephone}" . PHP_EOL;
        echo "Content:{$this->content}" . PHP_EOL;
        echo "SendId:{$this->senderId}" . PHP_EOL;
        echo "Values:" . json_encode($this->values) . PHP_EOL;
        $res = SmsPesoHelper::sendMarketing($this->telephone, $this->content, $this->values, $this->senderId, 789);
        echo "Res:" . $res . PHP_EOL;
    }

}
