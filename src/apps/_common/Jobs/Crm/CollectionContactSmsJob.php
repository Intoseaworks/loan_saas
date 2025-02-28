<?php

namespace Common\Jobs\Crm;

use Common\Jobs\Job;
use Common\Models\Collection\CollectionContactSmsLog;
use Common\Models\Crm\MarketingSmsLog;
use Common\Models\Crm\SmsTemplate;
use Common\Models\Notice\SmsTask;
use Common\Models\Notice\SmsTaskUser;
use Common\Models\Notice\SmsTaskUserSendLog;
use Common\Models\Sms\SmsLog;
use Common\Models\Upload\Upload;
use Common\Services\Upload\UploadServer;
use Common\Utils\Data\DateHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Sms\SmsEgpHelper;
use Common\Utils\Upload\OssHelper;
use Common\Validators\Validation;
use Yunhan\Utils\Env;

class CollectionContactSmsJob extends Job {

    public $queue = 'collection-contact-sms';
    public $tries = 3;
    public $collectionContactSms;
    public $value;

    public function __construct($collectionContactSms,$value) {
        $this->collectionContactSms = $collectionContactSms;
        $this->value = $value;
    }

    public function handle() {
            $sended = false;
            # 检查是否发送过
//            if (!$exists = CollectionContactSmsLog::model()->newQuery()
//                ->where("contact_sms_id", $this->collectionContactSms->id)
//                ->where("telephone", $this->collectionContactSms->sms_telephone)
//                ->where("admin_id", $this->collectionContactSms->user_id)
//                ->where("sms_template_id", $this->collectionContactSms->sms_template_id)
//                ->count()) {
            //区分催收短信eventid=1
            $eventId = 'CollectionContactSms';
            if (Env::isProd()) {
                SmsEgpHelper::helper()->sendMarketing($this->collectionContactSms->sms_telephone, $this->collectionContactSms->sms_centent,$this->value,$this->value['appName'],$eventId);
            } else {
                $content = $this->collectionContactSms->sms_centent;
                foreach ($this->value as $k => $v) {
                    echo $k.PHP_EOL.$v.PHP_EOL;
                    $content = str_replace("{{" . $k . "}}", $v, $content);
                }
                $log = [
                    "telephone" => $this->collectionContactSms->sms_telephone,
                    "event_id" => $eventId,
                    "send_content" => $content,
                    "created_at" => DateHelper::dateTime(),
                    "remark" => 'test_催收短信',
                    "type" => SmsLog::TYPE_SMS.'_collection_test',
                    "status" => 1 ,
                ];
                SmsLog::model()->create($log);
            }
            echo 'sended'.PHP_EOL;
            try {
    //                    if($sended){
    //                        CollectionContactSmsLog::model()->createModel([
    //                            "contact_sms_id" => $this->collectionContactSms->id,
    //                            "telephone" => $this->collectionContactSms->sms_telephone,
    //                            "content" => $smsTaskUser->sms_centent,
    //                            "status" => 1,
    //                            "merchant_id" => $task->merchant_id,
    //                            "sms_template_id" => $this->collectionContactSms->sms_template_id
    //                        ]);
    //                    }
            }catch (\Exception $exception) {
                print_r($exception->getLine()).PHP_EOL;
                print_r($exception->getMessage()).PHP_EOL;
            }
    //            }
    //            print_r($exists);
        echo PHP_EOL;
    }

}
