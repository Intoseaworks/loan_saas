<?php

namespace Common\Jobs\Crm;

use Admin\Services\Coupon\CouponReceiveServer;
use Admin\Services\Coupon\CouponTaskServer;
use Common\Jobs\Job;
use Common\Models\Coupon\Coupon;
use Common\Models\Coupon\CouponReceive;
use Common\Models\Coupon\CouponTask;
use Common\Models\Crm\SmsTemplate;
use Common\Models\Notice\NoticeUser;
use Common\Models\Notice\SmsTask;
use Common\Models\Notice\SmsTaskUser;
use Common\Models\Upload\Upload;
use Common\Models\User\User;
use Common\Services\Upload\UploadServer;
use Common\Validators\Validation;
use Yunhan\Utils\Env;

class CouponTaskIssueToRegisterUserJob extends Job {

    public $queue = 'coupon-task-user-reg';
    public $tries = 3;
    public $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function handle() {
        $whereCouponTasks = [];
        $whereCouponTasks[] = ['grant_type','=',1];
        $whereCouponTasks[] = ['status','=',1];
        $couponTasks = CouponTaskServer::server()->getCouponTasks($whereCouponTasks);
        foreach ($couponTasks as $couponTask){
            $coupon = Coupon::model()->getOne($couponTask->coupon_id);
            if ($coupon && $coupon->merchant_id == $this->user->merchant_id ){
                $params['coupon_id'] = $couponTask->coupon_id;
                $params['user_id'] = $this->user->id;
                $params['coupon_task_id'] = $couponTask->id;
                CouponReceiveServer::server()->addCouponReceive($params);
            }
        }
        echo '注册用户领用优惠券'.$this->user->id;
    }

}
