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
use Common\Utils\Sms\SmsEgpHelper;
use Common\Utils\Upload\OssHelper;
use Common\Validators\Validation;
use Yunhan\Utils\Env;

class SmsTaskUserJob extends Job {

//    public $queue = 'sms-task-user';
    public $tries = 3;
    public $smsTaskUserId;

    public function __construct($smsTaskUserId) {
        $this->smsTaskUserId = $smsTaskUserId;
    }

    public function handle() {
            $sended = false;
            # 检查是否发送过
            $smsTaskUser = SmsTaskUser::model()->getOne($this->smsTaskUserId);
            $task = SmsTask::model()->getOne($smsTaskUser->task_id);
            if (!$exists = SmsTaskUserSendLog::model()->newQuery()
                ->where("task_id", $smsTaskUser->task_id)
                ->where("telephone", $smsTaskUser->sms_telephone)
                ->where("status", 1)
                ->count()) {
                if (Env::isProd()) {
                    $sended = SmsEgpHelper::helper()->sendMarketing($smsTaskUser->telephone, $smsTaskUser->sms_centent);
                } else {
                    $sended = true;
                }
                echo 'sended'.PHP_EOL;
                try {
                    if($sended){
                        SmsTaskUserSendLog::model()->createModel([
                            "task_id" => $task->id,
                            "telephone" => $smsTaskUser->sms_telephone,
                            "content" => $smsTaskUser->sms_centent,
                            "status" => 1,
                            "merchant_id" => $task->merchant_id,
                            "sms_template_id" => $task->sms_template_id
                        ]);
                    }
                }catch (\Exception $exception) {
                    print_r($exception->getLine()).PHP_EOL;
                    print_r($exception->getMessage()).PHP_EOL;
                }
            }
            print_r($exists);
            echo PHP_EOL;
    }

}
