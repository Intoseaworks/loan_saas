<?php

namespace Common\Jobs\Crm;

use Common\Jobs\Job;
use Common\Models\Coupon\CouponReceive;
use Common\Models\Coupon\CouponTask;
use Common\Models\Crm\SmsTemplate;
use Common\Models\Inbox\Inbox;
use Common\Models\Notice\NoticeTemplate;
use Common\Models\Notice\NoticeUser;
use Common\Models\Notice\SmsTask;
use Common\Models\Notice\SmsTaskUser;
use Common\Models\Upload\Upload;
use Common\Models\User\User;
use Common\Services\Upload\UploadServer;
use Common\Utils\Sms\SmsEgpHelper;
use Common\Validators\Validation;
use Yunhan\Utils\Env;

class CouponTaskUserJob extends Job {

    public $queue = 'coupon-task-user';
    public $tries = 3;
    public $couponReceive;

    public function __construct(CouponReceive $res) {
        $this->couponReceive = $res;
    }

    public function handle() {
        $user = User::model()->getOne($this->couponReceive->user_id);
        $task = CouponTask::model()->getOne($this->couponReceive->coupon_task_id);
        if (!$task) {
            return;
        }
        //站内信通知优惠券发放
        if ( 2 == $task->notice_type ) {
            $noticUser = NoticeUser::model()->create(['user_id'=>$this->couponReceive->user_id,'task_id'=>$this->couponReceive->coupon_task_id,'notice_id'=>$task->notice_type_scope,
                                        'telephone'=>$user->telephone,'fullname'=>$user->fullname,'type'=>1]);
            if ($noticUser){
                // 生成私信
                $noticeTemplate = NoticeTemplate::model()->getOne($noticUser->notice_id);
                $inbox = Inbox::model()->create($this->couponReceive->user_id, $noticeTemplate->title, $noticeTemplate->content);
                if ($inbox){
                    $inbox->user_notice_id = $noticUser->id;
                    $inbox->save();
                }

            }
        }
        //短信通知优惠券发放
        if ( 3 == $task->notice_type ) {
            $smsTemplate = SmsTemplate::model()->getOne($task->notice_type_scope);
            if (!$smsTemplate) {
//            return;
            }
            $sended = false;
            # 检查是否发送过
            if (!$exists = NoticeUser::model()->newQuery()
                ->where("task_id", $task->id)
                ->where("user_id", $this->couponReceive->user_id)
                ->where("notice_id", $smsTemplate->id)
                ->count()) {
                if (Env::isProd()) {
                    $sended = SmsEgpHelper::helper()->sendMarketing($user->telephone, $smsTemplate->tpl_content);
                } else {
                    $sended = true;
                }
                echo 'sended'.PHP_EOL;
                if($sended){
                    NoticeUser::model()->create(['user_id'=>$this->couponReceive->user_id,'task_id'=>$this->couponReceive->coupon_task_id,'notice_id'=>$task->notice_type_scope,
                        'telephone'=>$user->telephone,'fullname'=>$user->fullname,'type'=>2]);
                }
            }
            print_r($exists);
        }
    }

}
