<?php

namespace Common\Jobs\Marketing;

use Common\Jobs\Job;
use Common\Models\Marketing\GsmSender;
use Common\Models\Marketing\GsmTask;
use Common\Models\Marketing\GsmTpl;
use Common\Utils\Sms\SmsPesoLocalGsmHelper;
use Common\Utils\Sms\SmsPesoLocalGsm2Helper;

class SendSmsJob extends Job {
    
    const PORT_MAP = [
        "3" => "1-7",#U1
        "4" => "8-14",#S2
        "6" => "15-17",#U3
        "2" => "18-24",#U4
        "1" => "25-30",#U5
        "7" => "31-32",#U6
    ];

    public $queue = 'marketing-gsm';
    public $tries = 1;
    public $taskId;
    public $telephone;
    public $tpl;
    public $sender;
    public $senderId;

    public function __construct($taskId, $telephone, $tpl, $sender, $senderId) {
        $this->taskId = $taskId;
        $this->telephone = $telephone;
        $this->tpl = $tpl;
        $this->sender = $sender;
        $this->senderId = $senderId;
    }

    public function handle() {
        $task = GsmTask::model()->getOne($this->taskId);
        echo "{$this->telephone}|{$this->sender}|{$this->tpl['tpl_content']}" . PHP_EOL;
//        $sendRes = SmsPesoLocalGsmHelper::helper()->sendMarketing($this->telephone, $this->tpl['tpl_content'], [], $this->sender);
        $portId = isset(self::PORT_MAP[$task->merchant_id]) ? self::PORT_MAP[$task->merchant_id] : "";
        $sendRes = SmsPesoLocalGsm2Helper::helper()->sendMarketing($this->telephone, $this->tpl['tpl_content'], [], $this->sender, 0, $portId);
        $task->telephone_sended_count += 1;
        $task->last_runtime_end = date("Y-m-d H:i:s");
        $task->save();
        \Common\Models\Marketing\GsmRuntime::model()->createModel([
            "task_id" => $task->id,
            "sender_id" => $this->senderId ?? "",
            "sender" => $this->sender,
            "telephone" => $this->telephone,
            "tpl_id" => $this->tpl['id'],
            "tpl_content" => $this->tpl['tpl_content'],
            "send_status" => $sendRes ? 1 : 0
        ]);
    }

}
