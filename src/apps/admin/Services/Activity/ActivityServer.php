<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Activity;

use Admin\Services\BaseService;
use Carbon\Carbon;
use Common\Models\Activity\Activity;
use Common\Models\Activity\ActivityAward;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use function array_get;

class ActivityServer extends BaseService {

    public function list($params) {
        $size = array_get($params, 'size');
        $type = array_get($params, 'type',Activity::INVITE_ACTIVIE);
        $query = Activity::model()->newQuery()->with('awards')->where("activities.type", $type);
        if (isset($params['title']) && trim($params['title'])) {
            $query->where("activities.title",'like', '%'.$params['title'].'%');
        }
        if (isset($params['status'])) {
            $query->where("activities.status", $params['status']);
        }
//        $query->select(['activities.*']);
        $query->orderByDesc("activities.id");
        return $query->paginate($size);
    }

    public function listAll() {
        $query = Activity::model()->newQuery()->where("activities.status", 1)->where('end_time','>',Carbon::now()->toDateTimeString());
        return $query->orderByDesc("id")->get(['id','title','end_time','created_at']);
    }

    public function view($id) {
        $query = Activity::model()->newQuery()->with('awards')->where("id", $id)->first();
        return $query;
    }

    public function addActivity($params) {
        //抽奖活动中奖概率和判断
        if ( isset($params['type']) && $params['type']==2 && isset($params['award_ids']) ){
            if ( ActivityAward::model()->whereIn('id',$params['award_ids'])->sum('award_use_limit') > 100 ){
                return $this->outputException("中奖概率和大于100%");
            }
        }
        if ( $params['end_time'] < $params['start_time'] ){
            return $this->outputException("截止日期小于开始日期");
        }
        $res = Activity::model()->createModel([
            "title" => $params['title'],
            "end_time" =>  $params['end_time'] ,
            "start_time" =>  $params['start_time'] ,
            'merchant_id' => MerchantHelper::getMerchantId(),
            'type' => array_get($params, 'type',Activity::INVITE_ACTIVIE)
        ]);
        if ($res) {
            $res->awards()->attach($params['award_ids']);
            return $this->outputSuccess();
        }
        return $this->outputException("保存失败");
    }

    public function setActivity($params){
        $Activity = Activity::model()->getOne($params['id']);
        //抽奖活动中奖概率和判断
        if ( $Activity->type==2 && isset($params['award_ids']) ){
            if ( ActivityAward::model()->whereIn('id',$params['award_ids'])->sum('award_use_limit') > 100 ){
                return $this->outputException("中奖概率和大于100%");
            }
        }
        if ( isset($params['end_time']) && isset($params['start_time']) &&  $params['end_time'] < $params['start_time'] ){
            return $this->outputException("截止日期小于开始日期");
        }
        if($Activity){
            $params['update_user'] = LoginHelper::getAdminId();
            if ( $Activity->saveModel($params) ) {
                $Activity->awards()->sync($params['award_ids']);
                return $this->outputSuccess();
            }else {
                return $this->outputError("Record update error");
            }
        }else{
            return $this->outputError("Record does not exist");
        }
    }
}
