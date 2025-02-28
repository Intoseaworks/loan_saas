<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Activity;

use Admin\Exports\Activity\ActivityRecordExport;
use Admin\Exports\Activity\ActivityRecordInviteExport;
use Admin\Models\Order\Order;
use Admin\Models\User\User;
use Admin\Services\BaseService;
use Admin\Services\Coupon\CouponReceiveServer;
use Common\Jobs\Crm\CouponTaskUserJob;
use Common\Models\Activity\ActivitiesRecord;
use Common\Models\Activity\ActivityAward;
use Common\Models\User\UserInviteCode;
use Common\Services\Order\OrderCheckServer;
use Common\Utils\LoginHelper;
use function array_get;
use Common\Utils\MerchantHelper;

class ActivitiesRecordServer extends BaseService {

    public function list($params) {
        if (trim(array_get($params, 'type'))){
            return $this->listBonus($params);
        }
        $size = array_get($params, 'size');
        $keyword = trim(array_get($params, 'keyword'));
        if ($keyword){
//            $data = ActivitiesRecord::whereHas('user',function($q) use ($keyword){
//                $q->whereTelephone($keyword)->orWhere('fullname', 'like', "%$keyword%");
//            })->with(['user'=> function($q) {
//                $q->withCount(['activitiesRecords']);
//            }])->with('activity.awards')->groupBy('activities_records.user_id')->paginate($size);
            $data = User::has('invitedUsers')->where(function ($q) use ($keyword){
                $q->whereTelephone($keyword)->orWhere('fullname', 'like', "%$keyword%");
            })->withCount(['invitedUsers'])->paginate($size);
        }else{
//            $data = ActivitiesRecord::with(['user'=> function($q){
//                $q->withCount(['activitiesRecords']);
//            }])->with('activity.awards')->groupBy('activities_records.user_id')->paginate($size);
            $data = User::has('invitedUsers')->withCount(['invitedUsers'])->paginate($size);
        }
        foreach ($data->items() as $item) {
            $item->activities_registered_count = $item->invited_users_count;
            $invited_user_ids = $item->invitedUsers->pluck('invited_user')->toArray();
            $item->activities_signed_count = OrderCheckServer::server()
                ->getOrderCount($invited_user_ids,0,array_merge([Order::STATUS_WAIT_SYSTEM_APPROVE,
                    Order::STATUS_WAIT_MANUAL_APPROVE,Order::STATUS_SYSTEM_APPROVING,Order::STATUS_WAIT_CALL_APPROVE,
                    Order::STATUS_WAIT_TWICE_CALL_APPROVE],Order::CONTRACT_STATUS,[Order::STATUS_FINISH]),[['signed_time','>=','2000-01-01 00:00:00']]);
            $item->activities_payed_count = OrderCheckServer::server()
                ->getOrderCount($invited_user_ids,0,Order::CONTRACT_STATUS);
            $item->activities_finish_count = OrderCheckServer::server()
                ->getOrderCount($invited_user_ids,0, [Order::STATUS_FINISH]);
            $item->user = $item->toArray();
        }
        return $data;
    }

    public function listBonus($params) {
        $size = array_get($params, 'size');
        $keyword = trim(array_get($params, 'keyword'));
        $activitiesRecord = ActivitiesRecord::whereHas('activity',function($q){
            $q->whereType(2);
        })->with('user')->with('activity.awards');
        if ($awardTime = array_get($params, 'award_time')) {
            if (count($awardTime) == 2) {
                $timeStart = current($awardTime);
                $timeEnd = last($awardTime);
                $activitiesRecord->whereBetween('activities_records.created_at', [$timeStart, $timeEnd]);
            }
        }
        if ($awardIds = array_get($params, 'award_ids')) {
//            $activitiesRecord->whereHas('activity.awards',function($q) use ($awardIds){
//                $q->whereIn('activities_awords.id',$awardIds);
//            });
            $activitiesRecord->whereIn('aword_id',$awardIds);
        }
        if ($keyword){
            $data = $activitiesRecord->whereHas('user',function($q) use ($keyword){
                $q->whereTelephone($keyword);
            })->orderBy('id','desc')->paginate($size);
//            $data = User::has('invitedUsers')->where(function ($q) use ($keyword){
//                $q->whereTelephone($keyword)->orWhere('fullname', 'like', "%$keyword%");
//            })->withCount(['invitedUsers'])->paginate($size);
        }else{
            $data = $activitiesRecord->orderBy('id','desc')->paginate($size);
//            $data = User::has('invitedUsers')->withCount(['invitedUsers'])->paginate($size);
        }
        foreach ($data->items() as $item){
            foreach ($item->activity->awards as $award){
                if ($award->id == $item->aword_id){
                    $item->award_title = $award->title;
                }
            }
        }
        return $data;
    }

    public function export($params) {
        $size = array_get($params, 'size');
        $res = ActivitiesRecord::with('user')->with('activity.awards')->get();
        ActivityRecordExport::getInstance()->export($res, ActivityRecordExport::SCENE_EXPORT,false);
    }

    public function exportInvite($params) {
        $keyword = trim(array_get($params, 'keyword'));
        if ($keyword){
            $data = ActivitiesRecord::whereHas('user',function($q) use ($keyword){
                $q->whereTelephone($keyword)->orWhere('fullname', 'like', "%$keyword%");
            })->with(['user'=> function($q) {
                $q->withCount(['activitiesRecords']);
            }])->with('activity.awards')->get();
        }else{
            $data = ActivitiesRecord::with(['user'=> function($q){
                $q->withCount(['activitiesRecords']);
            }])->with('activity.awards')->get();
        }
        foreach ($data as $item) {
            $item->user->activities_registered_count = 1;
            $item->user->activities_signed_count = 1;
            $item->user->activities_payed_count = 1;
            $item->user->activities_finish_count = 1;
        }
        ActivityRecordInviteExport::getInstance()->export($data, ActivityRecordInviteExport::SCENE_EXPORT,false);
    }

    public function view($id) {
        $query = ActivitiesRecord::model()->newQuery()->where("id", $id)->first();
        return $query;
    }

    public function addCouponReceive($params) {
        $res = ActivitiesRecord::model()->create($params);
        if ($res) {
//            统计邀请好友数量,成功注册数,成功完件数,放款数,结清数
//            $task = ActivityAward::model()->where('id',$params['coupon_task_id']);
//            $task->increment('issue_count');
//            $task->increment('received_count');
            return $this->outputSuccess();
        }
        return $this->outputException("保存失败");
    }

    //发放奖励
    public function awardBonus($activityType,$activityStatus,$awardCondition,$merchantId,$endTime,$userId){
        $activityAwardConditions = [];
        $activityAwardConditions[] = ['type','=',$activityType];
        $activityAwardConditions[] = ['status','=',$activityStatus];
        $activityAwardConditions[] = ['award_condition','=',$awardCondition];
        $activityAwardConditions[] = ['merchant_id','=',$merchantId];
        $activityConditions = [];
        $activityConditions[] = ['type','=',$activityType];
        $activityConditions[] = ['status','=',$activityStatus];
        $activityConditions[] = ['end_time','>',$endTime];
        $activityConditions[] = ['merchant_id','=',$merchantId];
        $awards = ActivityAwardServer::server()->getActivityAwards($activityAwardConditions,$activityConditions);
        foreach ($awards as $award){
            //已发放奖励不能再发,取消20210908
//            if (!ActivitiesRecord::model()->where('user_id',$userId)
//                ->where('status',2)
//                ->where('activity_id',$award->activities[0]->id)
//                ->where('aword_id',$award->id)
//                ->where('award_condition',$award->award_condition)
//                ->first()){
                $paramsCoupon['coupon_id'] = $award->award_value;
                $paramsCoupon['user_id'] = $userId;
                $paramsCoupon['coupon_task_id'] = 0;
                $res = CouponReceiveServer::server()->addCouponReceive($paramsCoupon);
                if ( $res->isSuccess() ){
                    $paramsActivitiesRecord['record_id'] = $res->getData()->id;
                    $paramsActivitiesRecord['user_id'] = $userId;
                    $paramsActivitiesRecord['status'] = 2;
                    $paramsActivitiesRecord['activity_id'] = $award->activities[0]->id;
                    $paramsActivitiesRecord['aword_id'] = $award->id;
                    $paramsActivitiesRecord['award_condition'] = $award->award_condition;
//                    dd($paramsActivitiesRecord);
                    $this->addCouponReceive($paramsActivitiesRecord);
                }
//            }
        }
    }

    //发放奖励
    public function awardBonusWithId($award,$userId){
//        dd($award);
            //已发放奖励不能再发,取消20210908
//            if (!ActivitiesRecord::model()->where('user_id',$userId)
//                ->where('status',2)
//                ->where('activity_id',$award->activities[0]->id)
//                ->where('aword_id',$award->id)
//                ->where('award_condition',$award->award_condition)
//                ->first()){
            $paramsCoupon['coupon_id'] = $award['award_value'];
            $paramsCoupon['user_id'] = $userId;
            $paramsCoupon['coupon_task_id'] = 0;
            $res = CouponReceiveServer::server()->addCouponReceive($paramsCoupon);
            if ( $res->isSuccess() ){
                $paramsActivitiesRecord['record_id'] = $res->getData()->id;
                $paramsActivitiesRecord['user_id'] = $userId;
                $paramsActivitiesRecord['status'] = 2;
                $paramsActivitiesRecord['activity_id'] = $award['pivot']['activity_id'];
                $paramsActivitiesRecord['aword_id'] = $award['id'];
                $paramsActivitiesRecord['award_condition'] = $award['award_condition'];
//                    dd($paramsActivitiesRecord);
                $this->addCouponReceive($paramsActivitiesRecord);
            }
//            }
    }
}
