<?php

namespace Common\Jobs\Marketing;

use Common\Jobs\Job;
use Common\Models\Marketing\GsmSender;
use Common\Models\Marketing\GsmTask;
use Common\Models\Marketing\GsmTpl;
use Common\Utils\Sms\SmsPesoLocalGsmHelper;

class GsmTaskJob extends Job {

    public $queue = 'marketing-gsm';
    public $tries = 1;
    public $taskId;

    public function __construct($taskId) {
        $this->taskId = $taskId;
    }

    public function handle() {
        if (!$this->taskId) {
            echo "任务ID未设置";
            return;
        }
        echo "开始发送TaskId:" . $this->taskId . PHP_EOL;
        $task = GsmTask::model()->getOne($this->taskId);
        if (!$task) {
            echo "任务未找到";
            return;
        }
        $sender = GsmSender::model()->getRand();
        $senderId = 0;
        if (!$sender) {
            $sender = "";
        }else{
            $senderId = $sender['id'];
            $sender = $sender['sender'];
        }
        $tpl = GsmTpl::model()->getRand($task->merchant_id);
        if (!$tpl) {
            echo "未配置Tpl";
            return;
        }
        if ($task->telephones) {
            $telephones = $task->getTelephones();
            foreach ($telephones as $telephone) {
                dispatch(new SendSmsJob($task->id, $telephone, $tpl, $sender, $senderId));
            }
        } else {
            echo "手机号为空";
            return;
        }
        $task->last_runtime_end = date("Y-m-d H:i:s");
        $task->save();
    }

}
