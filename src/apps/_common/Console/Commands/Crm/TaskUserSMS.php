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

class TaskUserSMS extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasktouser:sms {hour?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '短信任务定时脚本 hour = 整点“01”格式';

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
        $query = SmsTask::model()->newQuery();
        $query->where("status", SmsTask::STATUS_NORMAL);
//        $query->where(function($query) use ($date){
//            $query->where("send_time", "like", "%{$date}%");
//        });
        $res = $query->get();
        foreach ($res as $item) {
            echo "启动短信Task:{$item->id}".PHP_EOL;
            $smsTaskUserCount = SmsTaskUser::model()->where('task_id',$item->id)->count();
            $smsTaskUserSendedCount = SmsTaskUserSendLog::model()->where('task_id',$item->id)->count();
            //发送数据大于等于该发数据,任务已完成,不用再发
            if ($smsTaskUserSendedCount && $smsTaskUserSendedCount >= $smsTaskUserCount) {
                $item->sms_run_times = $smsTaskUserCount;
                $item->save();
                continue;
            }
            $this->dealTask($item->id);
        }
    }

    private function dealTask($taskId)
    {
        $task = SmsTask::model()->getOne($taskId);
        if (empty($task->sms_template_id)) {
            echo "短信模版为空,模版id:{$task->sms_template_id}".PHP_EOL;
            return;
        }
        $smsTemplate = SmsTemplate::model()->getOne($task->sms_template_id);
        if (!$smsTemplate) {
            echo "短信模版不存在:{$task->sms_template_id}".PHP_EOL;
            return;
        }
        if ($task->task_type == SmsTask::TYPE_SMS) {
            if (empty($task->upload_id)){
                echo "上传号码文件不存在:{$task->sms_template_id}".PHP_EOL;
                return;
            }
            $upload = Upload::model()->getOne($task->upload_id);
            $paths = (array)$upload->path;

            $tmpFileName = UploadServer::setDownloadTmpFileName($paths);
            UploadServer::resetDownloadTmp();

            $fileName = UploadServer::getFileName($upload->source_id, SmsTask::TYPE_SMS);

            if (!file_exists($tmpFileName)) {
                if (!$downloadFile = UploadServer::downloadFiles($paths, $tmpFileName)) {
                    return $this->outputException('下载失败');
                }
            }
            UploadServer::download($tmpFileName, false, $fileName);
            /**
             * 保存数据
             */
            $row = 0;
            if (($handle = fopen($tmpFileName, "r")) !== false) {
                $template = SmsTemplateServer::server()->getOne($task->sms_template_id);
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $num = count($data);
                    $row++;
                    for ($c = 0; $c < $num; $c++) {
                        if (Validation::validateMobile('', $data[$c])) {
                            echo $data[$c].PHP_EOL;
                            if (!$exists = SmsTaskUser::model()->newQuery()
                                ->where("sms_telephone", $data[$c])
                                ->where("task_id", $task->id)
                                ->count()){

                                //黑名单排除
                                if ( $task->check_blacklist ) {
                                    $item = [
                                        "telephone" => $data[$c]
                                    ];
                                    if (WhiteListServer::server()->checkBlackList($item)->count() > 0){
                                        echo '黑名单'.$data[$c].PHP_EOL;
                                        continue;
                                    }
                                }
                                //灰名单排除
                                if ( $task->check_greylist ) {
                                    if (UserBlack::model()->isActive()->whereTelephone($data[$c])->exists()) {
                                        echo '灰名单'.$data[$c].PHP_EOL;
                                        continue;
                                    }
                                }

                                $smsTaskUser = SmsTaskUser::model()->create([
                                    'sms_telephone' => $data[$c],
                                    'type' => 2,
                                    'task_id' => $task->id,
                                    'sms_date' => date('Y-m-d H:i:s'),
                                    'sms_centent'=>$template['tpl_content']
                                ]);
                                dispatch(new SmsTaskUserJob($smsTaskUser->id));
                            }
                        }
                    }
                }
                fclose($handle);
            } else {
                return $this->outputException('文件打开异常');
            }
        }
        dispatch(new SmsTaskUserStaticJob($taskId));
    }

}
