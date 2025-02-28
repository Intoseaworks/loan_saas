<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Coupon;

use Admin\Services\BaseService;
use Common\Jobs\Crm\CouponTaskUserJob;
use Common\Models\Coupon\CouponReceive;
use Common\Models\Coupon\CouponTask;
use Common\Utils\LoginHelper;
use function array_get;
use Common\Utils\MerchantHelper;

class CouponReceiveServer extends BaseService {

    public function list($params) {
        $size = array_get($params, 'size');
        $query = CouponReceive::model()->newQuery();
        $query = $query->join('coupon','coupon_receive.coupon_id','=','coupon.id');
        $query = $query->join('user','coupon_receive.user_id','=','user.id');
        $query = $query->join('coupon_task','coupon_receive.coupon_task_id','=','coupon_task.id');
        $query = $query->join('order','coupon_receive.order_id','=','order.id');
        if (isset($params['title']) && trim($params['title'])) {
            $query->where("coupon.title",'like', '%'.$params['title'].'%');
        }
        //券使用时间
        if ($authCompleteTime = array_get($params, 'use_time')) {
            $authCompleteTimeStart = current($authCompleteTime);
            $authCompleteTimeEnd = last($authCompleteTime);
            $query->whereBetween('coupon_receive.use_time', [$authCompleteTimeStart, $authCompleteTimeEnd]);
        }
        $query->where('coupon.merchant_id',MerchantHelper::getMerchantId());
        // $query->select(['coupon_receive.*','user.*','order.*','coupon_task.*','coupon.*']);
        $query->select(['coupon_receive.*','user.telephone','coupon.title','coupon.coupon_type','coupon.used_amount','order.order_no']);
        $query->orderByDesc("coupon_receive.use_time");
        return $query->paginate($size);
    }

    public function listCustom($params) {
        $size = array_get($params, 'size');
        $query = CouponReceive::model()->newQuery();
        $query = $query->join('coupon','coupon_receive.coupon_id','=','coupon.id');
        $query = $query->join('merchant','merchant.id','=','coupon.merchant_id');
        $query = $query->join('user','coupon_receive.user_id','=','user.id');
        $query = $query->join('coupon_task_custom_import','coupon_receive.coupon_task_custom_id','=','coupon_task_custom_import.id');
        $query = $query->leftJoin('order','coupon_receive.order_id','=','order.id');
        if (isset($params['title']) && trim($params['title'])) {
            $query->where(function ($query) use ($params){
                $query->where("coupon.title",'like', '%'.$params['title'].'%')->orWhere("user.telephone",'like', '%'.$params['title'].'%');
            });
        }
        //券使用时间
        if ($authCompleteTime = array_get($params, 'create_at')) {
            $authCompleteTimeStart = current($authCompleteTime);
            $authCompleteTimeEnd = last($authCompleteTime);
            $query->whereBetween('coupon_receive.created_at', [$authCompleteTimeStart, $authCompleteTimeEnd]);
        }
        if (isset($params['merchant_id'])){
            $query->whereIn('coupon.merchant_id',$params['merchant_id']);
        }
        // $query->select(['coupon_receive.*','user.*','order.*','coupon_task.*','coupon.*']);
        $query->select(['coupon_receive.*','user.telephone',\DB::raw("merchant.product_name as merchant_name"),'coupon.merchant_id','coupon.title','coupon.coupon_type','coupon.used_amount','order.order_no']);
        $query->orderByDesc("coupon_receive.created_at");
        return $query->paginate($size);
    }

    public function view($id) {
        $query = CouponReceive::model()->newQuery()->where("id", $id)->first();
        return $query;
    }

    public function addCouponReceive($params) {
        $res = CouponReceive::model()->create($params);
        if ($res) {
            $task = CouponTask::model()->where('id',$params['coupon_task_id']);
            $task->increment('issue_count');
            $task->increment('received_count');
            //发送成功优惠券,发送短信和站内信job
            dispatch(new CouponTaskUserJob($res));
            return $this->outputSuccess("保存成功",$res);
        }
        return $this->outputException("保存失败");
    }

    public function setCouponReceive($params){
        $coupon = CouponReceive::model()->getOne($params['id']);
        if($coupon){
            $params['update_user'] = LoginHelper::getAdminId();
            if ( $coupon->saveModel($params) ) {
                return $this->outputSuccess();
            }else {
                return $this->outputError("Record update error");
            }
        }else{
            return $this->outputError("Record does not exist");
        }
    }
}
