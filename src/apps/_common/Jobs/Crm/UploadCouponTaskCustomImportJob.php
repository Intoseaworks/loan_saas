<?php

namespace Common\Jobs\Crm;


use Carbon\Carbon;
use Common\Jobs\Job;
use Common\Models\Coupon\CouponTaskCustomImport;

class UploadCouponTaskCustomImportJob extends Job {

//    public $queue = 'collection_sms_template';
    public $tries = 3;
    private $_list;
    private $_adminId;
    private $_sendTime;

    public function __construct($sendTime,$res, $adminId) {
        $this->_list = $res;
        $this->_adminId = $adminId;
        $this->_sendTime = $sendTime;
    }

    public function handle() {
        foreach ($this->_list[0] as $i => $item) {
            if ($i > 0) {
                if(!$item[0] && !$item[1] && !$item[2]){
                    continue;
                }
                if ( $coupon = \DB::table('coupon')->whereMerchantId($item[0])->whereTitle($item[2])->first() ){
                    $model = [
                        "merchant_id" => $item[0],
                        'telephone' => $item[1],
                        'coupon_title' => $item[2],
                        'coupon_id' => $coupon->id,
                        'create_user' => $this->_adminId,
                        'update_user' => $this->_adminId,
                        'send_time' => $this->_sendTime
                    ];
                    $res = CouponTaskCustomImport::model()->createModel($model);
                    if ($res){
                        //立即发送
                        if ( $model['send_time'] <= date('Y-m-d H:i:s') ){
                            dispatch(new CouponTaskCustomIssueToUserJob($res));
                        }else{
                            dispatch((new CouponTaskCustomIssueToUserJob($res))->delay(Carbon::parse()->diffInSeconds($model['send_time'])));
                        }
                    }
                }
            }
        }
    }

}
