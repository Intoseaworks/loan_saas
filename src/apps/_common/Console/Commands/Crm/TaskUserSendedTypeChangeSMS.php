<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Crm;

use Admin\Services\Crm\SmsTemplateServer;
use Admin\Services\Crm\WhiteListServer;
use Common\Jobs\Crm\SmsTaskUserJob;
use Common\Jobs\Crm\SmsTaskUserStaticJob;
use Common\Models\Crm\SmsTemplate;
use Common\Models\Notice\SmsTask;
use Common\Models\Notice\SmsTaskUser;
use Common\Models\Notice\SmsTaskUserSendLog;
use Common\Models\Upload\Upload;
use Common\Models\User\User;
use Common\Models\User\UserBlack;
use Common\Services\Upload\UploadServer;
use Common\Validators\Validation;
use Illuminate\Console\Command;

class TaskUserSendedTypeChangeSMS extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasktouser:sms:Sended {hour?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '短信发送状态变更定时脚本 hour = 整点“01”格式';

    public function handle() {
        if ($date = $this->argument('hour')) {
            $date = $date.":";
        }else{
            $date = date("H:");
        }
        $this->send($date);
        echo "End";
    }

    private function send($date) {
        echo $date;
        $query = SmsTaskUser::model()->newQuery();
        $query->where("type", SmsTaskUser::TYPE_SEND);
//        $query->where(function($query) use ($date){
//            $query->where("send_time", "like", "%{$date}%");
//        });
        $res = $query->get();
        foreach ($res as $item) {
            echo "启动短信状态变更:{$item->id}".PHP_EOL;
            $this->dealTask($item);
        }
    }

    private function dealTask($smsTaskUser)
    {
        if (SmsTaskUserSendLog::model()->where('task_id',$smsTaskUser->task_id)->where('telephone',$smsTaskUser->sms_telephone)->exists()){
            $smsTaskUser->type = 1;
            $smsTaskUser->save();
        }
    }

}
