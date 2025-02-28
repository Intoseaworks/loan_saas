<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Activity;

use Admin\Models\Upload\Upload;
use Admin\Models\User\UserBlack;
use Admin\Services\BaseService;
use Common\Models\Activity\ActivityAward;
use Common\Models\Crm\CustomerStatus;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use function array_get;
use Common\Utils\Upload\OssHelper;

class ActivityAwardServer extends BaseService {

    public function list($params) {
        $size = array_get($params, 'size');
        $query = ActivityAward::model()->newQuery();
        if (isset($params['type']) && trim($params['type'])) {
            $query->where("type",trim($params['type']));
        }else{
            $query->where("type",1);
        }
        $query->select();
        $query->orderByDesc("id");
        return $query->paginate($size);
    }

    public function listAll($params) {
        $query = ActivityAward::model()->newQuery()->where("status", 1);
        if (isset($params['type']) && trim($params['type'])) {
            $query->where("type",trim($params['type']));
        }else{
            $query->where("type",1);
        }
        return $query->orderByDesc("id")->get(['id','title']);
    }

    public function view($id) {
        $query = ActivityAward::model()->newQuery()->where("id", $id)->first();
        if ( $query->upload_id ){
            $query->file = $this->getUploadFile($query->upload_id);
        }
        return $query;
    }

    public function getUploadFile($id) {
        $upload = Upload::model()->getOne($id);
        $upload->url = OssHelper::helper()->picTokenUrl($upload->path);
        return $upload->getText()->url;
    }

    public function addActivityAward($params) {
        $params['update_user'] = LoginHelper::getAdminId();
        $params['create_user'] = LoginHelper::getAdminId();
        $params['batch'] = isset($params['batch']) ? implode(',',$params['batch']):null;
        $params['batch'] = isset($params['batch']) ? implode(',',$params['batch']):null;
        $params['merchant_id'] = MerchantHelper::getMerchantId();
        //同一个奖励条件判断
        if (isset($params['type']) && !empty(trim($params['type']))){

        }else{
            if (ActivityAward::model()->whereType(1)->where('award_condition',$params['award_condition'])->whereStatus(1)->first()){
                return $this->outputError('相同条件奖励已存在');
            };
        }
        $res = ActivityAward::model()->create($params);
        if ($res) {
            return $this->outputSuccess();
        }
        return $this->outputException("保存失败");
    }

    public function precountActivityAward($params) {
        //试算
        $precount = 0;
        $query = CustomerStatus::model()->has('user')->has('customer')->with('customer');
        //不是全体客户都发优惠券时候要加客户条件
        if ( 0 != $params['customer_group'] ) {
            $query = $query->where('crm_customer_status.type','=',$params['customer_group']);
        }
        if (!empty($params['cust_status'])) {
            $query->whereIn('crm_customer_status.status',explode(',',$params['cust_status']));
        }
        if (!empty($params['clm_level'])) {
            $query->whereIn('crm_customer_status.clm_level',explode(',',$params['clm_level']));
        }
        //最大逾期天数
        if ( !empty($params['max_overdue_day']) ) {
            $query->where('crm_customer_status.max_overdue_days','<=',$params['max_overdue_day']);
        }
        //特定日期发放
//        if ( !empty(trim($params['grant_type'])) && 4 == $params['grant_type'] && !empty(trim($params['grant_type_scope']))
//            && Carbon::parse($params['grant_type_scope'])->toDateString() != Carbon::now()->toDateString() ) {
//            return $this->outputSuccess('',$precount);
//        }
        //首贷普通名单,首贷白名单,批次
        if ( !empty(trim($params['customer_group'])) && (1 == $params['customer_group'] || 2 == $params['customer_group']) && !empty(trim($params['batch']))   ) {
            $query->whereIn('crm_customer_status.batch_id',$params['batch']);
        }
        $customers = $query->get(['crm_customer_status.*']);
//        dd($customers->count());
        foreach ($customers as $customer) {
//            $params['user_id'] = $customer->main_user_id;
//            $user = User::model()->getOne($params['user_id']);
//            if (!$user) {
//                //用户不存在
//                continue;
//            }
//            if ( $user && $mechantId != $user->merchant_id ) {
//                //品牌不一样,跳过
//                continue;
//            }
//            echo 'user_id为'.$customer->main_user_id.PHP_EOL;
//            echo $Activity->merchant_id.'--'.$user->merchant_id;
            $realCustomer = $customer->customer;
            //黑名单排除
            if ( $params['is_blacklist'] ) {
                $res = \Admin\Services\Crm\CustomerServer::server()->isBlack($realCustomer);
                if ($res) {
//                    echo '黑名单'.PHP_EOL;
                    continue;
                }
            }
            //灰名单排除
            if ( $params['is_graylist'] ) {
                if (UserBlack::model()->isActive()->whereTelephone($realCustomer->telephone)->exists()) {
//                    echo '灰名单'.PHP_EOL;
                    continue;
                }
            }
            //状态停留超过多少天发放
//            try {
//                $stopDays = CustomerServer::server()->getCrmCustomer($customer->user)->getStatusStopDays();
//            }catch (\Exception $exception){
////                    dd($exception);
////                    exit;
//            }

//            if ( 2 == $params['grant_type'] && $stopDays <= $params['grant_type_scope'] ) {
//////                    echo '状态停留不符和要求'.PHP_EOL;
////                continue;
////            }
            //生日当天发放
            if ( 3 == $params['grant_type'] && DateHelper::format($realCustomer->birthday,'m-d') != DateHelper::format(Carbon::now()->toDateString(),'m-d') ) {
//                echo '生日当天不符和要求'.PHP_EOL;
                continue;
            }
            //特定日期发放
//                if ( 4 == $params['grant_type'] && Carbon::parse($params['grant_type_scope'])->toDateString() != Carbon::now()->toDateString() ) {
////                echo $Award->id.PHP_EOL;
////                echo '特定日期发放不符和要求'.PHP_EOL;
//                continue;
//            }
            //当前全品牌最大逾期天数限定
//            if ( !empty($params['max_overdue_day']) && $customer->max_overdue_days > $params['max_overdue_day'] ) {
////                echo '当前全品牌最大逾期天数限定不符和要求'.PHP_EOL;
//                continue;
//            }
            //最后登录时间距今限定
            if ( !empty(trim($params['last_login_limit_scope'])) && !empty($customer->last_login_time ) ) {
                $days = explode('-',trim($params['last_login_limit_scope']));
                if ( $days[1]>0 ) {
                    $current = Carbon::now();
                    $loginDay = $current->diffInSeconds($customer->last_login_time);         // 6
                    if ( ($loginDay < $days[0]*24*3600 || $loginDay > $days[1]*24*3600) ) {
//                        echo '最后登录时间距今限定不符和要求'.PHP_EOL;
                        continue;
                    }
                }
            }
            //首贷普通名单,首贷白名单,批次
//            if ( $customer->batch_id >0 && (1 == $params['customer_group'] || 2 == $params['customer_group']) ) {
//                if ( !in_array($customer->batch_id,$params['batch']) ) {
////                    echo '首贷普通名单,首贷白名单,批次不符和要求'.PHP_EOL;
//                    continue;
//                }
//            }
//            if ( $user && $mechantId == $user->merchant_id ) {
//                $precount ++;
//            }
            $precount ++;
        }
        return $this->outputSuccess('',$precount);
    }

    public function setActivityAward($params){
        $Activity = ActivityAward::model()->getOne($params['id']);
        if ( $Activity->type==1 ){
            if ( isset($params['status']) && $params['status']==1  && ActivityAward::model()->whereType($Activity->type)->where('award_condition',$Activity->award_condition)->whereStatus(1)->first()){
                return $this->outputError('相同条件奖励已存在');
            };
        }
        if($Activity){
            $params['update_user'] = LoginHelper::getAdminId();
            $params['batch'] = isset($params['batch']) ? implode(',',$params['batch']):null;
            if ( !(isset($params['upload_id']) && $params['upload_id'] > 0) ){
                unset($params['upload_id']);
            }
            if ( $Activity->saveModel($params) ) {
                return $this->outputSuccess();
            }else {
                return $this->outputError("Record update error");
            }
        }else{
            return $this->outputError("Record does not exist");
        }
    }

    /**
     * @param array $conditions
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getActivityAwards($conditions,$activityConditions){
        $query = ActivityAward::model()->newQuery()->where($conditions)->whereHas('activities',function($q) use ($activityConditions){
            $q->where($activityConditions);
        });
        $query->orderByDesc("id");
        return $query->get();
    }

    public function getCustomers(ActivityAward $Award, $merchant_id=null){
        $query = CustomerStatus::model()->newQuery();
        if ($merchant_id){
            $query = $query->whereMerchantId($merchant_id);
        }
        //不是全体客户都发优惠券时候要加客户条件
        if ( 0 != $Award->customer_group ) {
            $query = $query->where('type','=',$Award->customer_group);
        }
        if (!empty($Award->cust_status)) {
            $query->whereIn('status',explode(',',$Award->cust_status));
        }
        if (!empty($Award->clm_level)) {
            $query->whereIn('clm_level',explode(',',$Award->clm_level));
        }
        return $query->orderByDesc("id")->get();
    }
}
