<?php

namespace Common\Jobs\Crm;

use Admin\Services\Coupon\CouponReceiveServer;
use Admin\Services\Coupon\CouponTaskServer;
use Common\Jobs\Job;
use Common\Models\Coupon\Coupon;
use Common\Models\Coupon\CouponReceive;
use Common\Models\Coupon\CouponTask;
use Common\Models\Coupon\CouponTaskCustomImport;
use Common\Models\Crm\SmsTemplate;
use Common\Models\Notice\NoticeUser;
use Common\Models\Notice\SmsTask;
use Common\Models\Notice\SmsTaskUser;
use Common\Models\Upload\Upload;
use Common\Models\User\User;
use Common\Services\Upload\UploadServer;
use Common\Validators\Validation;
use Yunhan\Utils\Env;

class CouponTaskCustomIssueToUserJob extends Job {

//    public $queue = 'coupon-task-user-reg';
    public $tries = 3;
    public $import;

    public function __construct(CouponTaskCustomImport $import) {
        $this->import = $import;
    }

    public function handle() {
        if ( $user = \DB::table('user')->whereMerchantId($this->import->merchant_id)->whereTelephone($this->import->telephone)->first() ){
            $params['coupon_id'] = $this->import->coupon_id;
            $params['user_id'] = $user->id;
            $params['coupon_task_id'] = 0;
            $params['coupon_task_custom_id'] = $this->import->id;
            $res = CouponReceiveServer::server()->addCouponReceive($params);
            if ( $res->isSuccess() ){
                $this->import->status = 2;
                $this->import->save();
            }
            echo '优惠券自定义发送'.$user->id;
        }else{
            echo '优惠券自定义发送用户不存在商户---'.$this->import->merchant_id.'--电话--'.$this->import->telephone;
        }
    }

}
