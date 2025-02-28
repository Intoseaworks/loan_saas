<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Coupon;

use Admin\Imports\Crm\CouponTaskCustomListImport;
use Admin\Models\User\User;
use Admin\Models\User\UserBlack;
use Admin\Services\BaseService;
use Carbon\Carbon;
use Common\Jobs\Crm\CouponTaskCustomIssueToUserJob;
use Common\Models\Coupon\CouponTask;
use Common\Models\Coupon\CouponTaskCustomImport;
use Common\Models\Crm\Customer;
use Common\Models\Crm\CustomerStatus;
use Common\Services\Crm\CustomerServer;
use Common\Utils\Data\StringHelper;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Third\AirudderHelper;
use Risk\Common\Models\Third\ThirdDataAirudder;
use Maatwebsite\Excel\Facades\Excel;
use function array_get;

class CouponTaskServer extends BaseService {

    public function list($params) {
        $size = array_get($params, 'size');
        $query = CouponTask::model()->newQuery();
        $query = $query->join('coupon','coupon_task.coupon_id','=','coupon.id');
        $query->where('coupon.merchant_id',MerchantHelper::getMerchantId());
        if (isset($params['title']) && trim($params['title'])) {
            $query->where("coupon_task.title",'like', '%'.$params['title'].'%');
        }
        $query->select(['coupon_task.*','coupon.coupon_type','coupon.used_amount']);
        $query->orderByDesc("id");
        $data = $query->paginate($size);
        foreach ($data->items() as $item){
            if ($item->batch){
                $sql = 'SELECT
                            count(*) as count_receive
                        FROM
                            `coupon_receive`,
                            crm_customer_status,
                            coupon_task,
                            `user` 
                        WHERE
                            `coupon_receive`.user_id = `user`.id
                            and crm_customer_status.main_user_id=coupon_receive.user_id
                            and crm_customer_status.batch_id in ('.implode(',',$item->batch).') 
                            and `coupon_receive`.coupon_task_id=coupon_task.id';
                $result = \DB::select($sql);
                $item->issue_count = $item->received_count = $result[0]->count_receive;
                //算了就存下
                $item->save();
            }
        }
        return $data;
    }

    public function view($id) {
        $query = CouponTask::model()->newQuery()->where("id", $id)->first();
        return $query;
    }

    public function addCouponTask($params) {
        $params['update_user'] = LoginHelper::getAdminId();
        $params['create_user'] = LoginHelper::getAdminId();
        $params['batch'] = isset($params['batch']) ? implode(',',$params['batch']):null;
        $res = CouponTask::model()->create($params);
        if ($res) {
            return $this->outputSuccess();
        }
        return $this->outputException("保存失败");
    }

    public function addCouponTaskCustom($params) {
        $params['update_user'] = LoginHelper::getAdminId();
        $params['create_user'] = LoginHelper::getAdminId();
        $phones = explode(',',$params['telephones']);
        foreach ( $phones as $k=>$item ){
            $phones[$k] = StringHelper::formatTelephone($item);
            if ( strlen($phones[$k])!=10 ){
                return $this->outputError("mobile number format is wrong");
            }
        }
        foreach ( $phones as $item ){
            $params['telephone'] = $item;
            $res = CouponTaskCustomImport::model()->create($params);
            if ($res){
                //立即发送
                if ( $params['send_time'] <= date('Y-m-d H:i:s') ){
                    dispatch(new CouponTaskCustomIssueToUserJob($res));
                }else{
                    dispatch((new CouponTaskCustomIssueToUserJob($res))->delay(Carbon::parse()->diffInSeconds($params['send_time'])));
                }
            }
        }
        if ($res) {
            return $this->outputSuccess();
        }
        return $this->outputException("保存失败");
    }

    public function upload($batchInfo) {
        $res = Excel::toArray(new CouponTaskCustomListImport(), $batchInfo['file']);
        dispatch(new \Common\Jobs\Crm\UploadCouponTaskCustomImportJob($batchInfo['send_time'],$res, LoginHelper::getAdminId()));
        return $this->outputSuccess("success " . count($res[0]) . "!");
    }

    public function precountCouponTask($params) {
        //试算
        $precount = 0;
        $query = CustomerStatus::model()->has('user')->has('customer')->with('customer');
        //不是全体客户都发优惠券时候要加客户条件
        if ( isset($params['customer_group']) && 0 != $params['customer_group'] ) {
            $query = $query->where('crm_customer_status.type','=',$params['customer_group']);
        }
        if ( isset($params['cust_status']) && !empty($params['cust_status'])) {
            $query->whereIn('crm_customer_status.status',explode(',',$params['cust_status']));
        }
        if ( isset($params['clm_level']) && !empty($params['clm_level'])) {
            $query->whereIn('crm_customer_status.clm_level',explode(',',$params['clm_level']));
        }
        //最大逾期天数
        if ( isset($params['max_overdue_day']) && !empty($params['max_overdue_day']) ) {
            $query->where('crm_customer_status.max_overdue_days','<=',$params['max_overdue_day']);
        }
        //特定日期发放
//        if ( !empty(trim($params['grant_type'])) && 4 == $params['grant_type'] && !empty(trim($params['grant_type_scope']))
//            && Carbon::parse($params['grant_type_scope'])->toDateString() != Carbon::now()->toDateString() ) {
//            return $this->outputSuccess('',$precount);
//        }
        //首贷普通名单,首贷白名单,批次
        if ( (isset($params['customer_group']) && !empty(trim($params['customer_group']))) &&
            ( isset($params['customer_group']) && (1 == $params['customer_group'] || 2 == $params['customer_group']) ) &&
             isset($params['batch'])  ) {
            $query->whereIn('crm_customer_status.batch_id',$params['batch']);
        }
        return $this->outputSuccess('',$query->count());
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
//            echo $coupon->merchant_id.'--'.$user->merchant_id;
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
////                echo $task->id.PHP_EOL;
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

    public function setCouponTask($params){
        $coupon = CouponTask::model()->getOne($params['id']);
        if($coupon){
            $params['update_user'] = LoginHelper::getAdminId();
            $params['batch'] = isset($params['batch']) ? implode(',',$params['batch']):null;
            if ( $coupon->saveModel($params) ) {
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
    public function getCouponTasks($conditions){
        $query = CouponTask::model()->newQuery()->where($conditions);
        $query->orderByDesc("id");
        return $query->get();
    }

    public function getCustomers(CouponTask $task,$merchant_id=null){
        //发券优化
        $query = CustomerStatus::model()->newQuery()->where('main_user_id','<>',0);
        if ($merchant_id){
            $query = $query->whereMerchantId($merchant_id);
        }
        //不是全体客户都发优惠券时候要加客户条件
        if ( 0 != $task->customer_group ) {
            $query = $query->where('type','=',$task->customer_group);
        }
        if (!empty($task->cust_status)) {
            $query->whereIn('status',explode(',',$task->cust_status));
        }
        if (!empty($task->clm_level)) {
            $query->whereIn('clm_level',explode(',',$task->clm_level));
        }
        //优化发券
        if ( is_array($task->batch) && count($task->batch) ) {
            $query->whereIn('batch_id',$task->batch);
        }
        return $query->orderByDesc("id")->get();
    }
}
