<?php

namespace Common\Jobs\Crm;

use Common\Jobs\Job;
use Common\Models\Crm\MarketingSmsLog;
use Common\Models\Crm\SmsTemplate;
use Common\Models\Notice\SmsTask;
use Common\Models\Notice\SmsTaskUser;
use Common\Models\Notice\SmsTaskUserSendLog;
use Common\Models\Upload\Upload;
use Common\Services\Upload\UploadServer;
use Common\Utils\MerchantHelper;
use Common\Utils\Upload\OssHelper;
use Common\Validators\Validation;
use Yunhan\Utils\Env;

class SmsTaskUserStaticJob extends Job {

//    public $queue = 'sms-task-user';
    public $tries = 3;
    public $taskId;

    public function __construct($taskId) {
        $this->taskId = $taskId;
    }

    public function handle() {
        $task = SmsTask::model()->getOne($this->taskId);
        $sendedUser = SmsTaskUserSendLog::model()->where('task_id',$this->taskId)->pluck('telephone');
        $task->sms_run_times = count($sendedUser);
        $task->save();
    }

}
