<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 16:47
 */

namespace Api\Controllers\User;

use Admin\Services\Activity\ActivitiesRecordServer;
use Admin\Services\Activity\ActivityAwardServer;
use Admin\Services\Coupon\CouponServer;
use Api\Rules\User\UserInfoRule;
use Carbon\Carbon;
use Common\Models\Activity\ActivitiesRecord;
use Common\Models\Activity\Activity;
use Common\Models\User\UserInviteCode;
use Common\Models\User\UserInviteFriend;
use Common\Response\ApiBaseController;
use Common\Utils\Data\StringHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\ShareCodeHelper;

class UserInviteController extends ApiBaseController {

    public function invitedUser() {
        $user = $this->identity();
        return $this->resultSuccess(UserInviteCode::model()->getInvitedUser($user->id));
    }

    public function inviteCode() {
        $user = $this->identity();
        $code = UserInviteCode::model()->getCodeByUser($user->id);
        $res = [];
        if ( config('config.invite_user') ){
            if ($code){
                $res['invite_code'] = $code->invite_code;
            }else{
                $res['invite_code'] = ShareCodeHelper::idToCode($user->id);
                UserInviteCode::createModel(['invite_code'=>$res['invite_code'],'user_id'=>$user->id]);
            }
            $res['invite_code'] = 'https://activity.peranyo.com/#/invite?invite_code='.$res['invite_code'];
        }else{
            $res['invite_code'] = null;
        }
        $activity = Activity::model()->with('awards')->where("activities.status", 1)->where('end_time','>',Carbon::now()->toDateTimeString())
            ->where("activities.type", 1)
            ->orderByDesc("id")->first();
        $res['invite_award'] = ['register'=>'--','apply_a_loan'=>'--','disbursement'=>'--','on_time_repayment'=>'--'];
        if ($activity){
            $res['activity_time'] = $activity->start_time.' -- '.$activity->end_time;
            foreach ($activity->awards as $award){
                switch ($award->award_condition) {
                    case 1:
                        if ($award->status){
                            $res['invite_award']['register'] = CouponServer::server()->view($award->award_value)->used_amount;
                        }
                        break;
                    case 2:
                        if ($award->status){
                            $res['invite_award']['apply_a_loan'] = CouponServer::server()->view($award->award_value)->used_amount;
                        }
                        break;
                    case 3:
                        if ($award->status){
                            $res['invite_award']['disbursement'] = CouponServer::server()->view($award->award_value)->used_amount;
                        }
                        break;
                    case 4:
                        if ($award->status){
                            $res['invite_award']['on_time_repayment'] = CouponServer::server()->view($award->award_value)->used_amount;
                        }
                        break;
                }
            }
        }else{
            $res['invite_code'] = null;
            $res['activity_time'] = 'Activity has not started yet';
        }
        return $this->resultSuccess($res);
    }

    public function inviteFriendsCashbackConfig() {
        $user = $this->identity();
        $res = [];
        $inviteFriendsCashbackConfig = config('config.invite_friends_cashback');
        if ( $inviteFriendsCashbackConfig['isOn'] ){
            $res['gcash'] = $user->telephone;
            $res['activity_config'] = true;
            $res['date'] = $inviteFriendsCashbackConfig['date'];
            $res['invite_count'] = UserInviteFriend::whereUserId($user->id)->whereCashBack(1)->count();
        }else{
            $res['gcash'] = null;
            $res['activity_config'] = false;
            $res['date'] = 'The activity is over, look forward to the next round!';
            $res['invite_count'] = null;
        }
        return $this->resultSuccess($res);
    }

    /**
     * 邀请好友返现
     *
     * @param UserInfoRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Common\Exceptions\RuleException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function inviteFriendsCashback(UserInfoRule $rule)
    {
        $user = $this->identity();
        /* 手机号过滤( ) - 处理*/
        $data = $this->request->all();
        if ($contactTelephone1 = array_get($data, 'contactTelephone1')) {
            $data['contactTelephone1'] = StringHelper::formatTelephone($contactTelephone1);
        }
        if ($contactTelephone2 = array_get($data, 'contactTelephone2')) {
            $data['contactTelephone2'] = StringHelper::formatTelephone($contactTelephone2);
        }
        if ($contactTelephone3 = array_get($data, 'contactTelephone3')) {
            $data['contactTelephone3'] = StringHelper::formatTelephone($contactTelephone3);
        }
        if (!$rule->validate($rule::SCENARIO_CREATE_USER_INVITE_FRIEND, $data)) {
            return $this->resultFail($rule->getError());
        }
        //号码验证, 号码不能为自己的手机
        $tels = [$data['contactTelephone1'],$data['contactTelephone2'],$data['contactTelephone3']];
        if ( in_array($user->telephone,$tels) ){
            return $this->resultFail("Couldn't fill your Phone No.");
        }
        if ( count(array_unique($tels))<3 ){
            return $this->resultFail("Couldn't fill same Phone No.");
        }
        if ( $friend = UserInviteFriend::whereUserId($user->id)->whereIn('telephone',$tels)->first() ){
            return $this->resultFail("You have already invited this friend, please invite others（Frinend's cellphone number {$friend->telephone})");
        }
        $friends = [];
        foreach ($tels as $tel){
            $friends[] = [
                'user_id'=>$user->id,'telephone'=>$tel,'merchant_id'=>$user->merchant_id,'gcash'=>$data['gcash']
            ];
        }
        UserInviteFriend::createModels($friends);
        return $this->resultSuccess();
    }

    public function bonusActivity() {
        $user = $this->identity();
        $activity = Activity::model()->with('awards')->where("activities.status", 1)->where('end_time','>',Carbon::now()->toDateTimeString())
            ->where("activities.type", 2)
            ->orderByDesc("id")->first();
        if ($activity){
            foreach ($activity->awards as $award){
                if ( $award->upload_id ){
                    $award->file = ActivityAwardServer::server()->getUploadFile($award->upload_id);
                }
            }
            $activity->bonus_count = $user->bonus_count;
        }
        return $this->resultSuccess($activity);
    }

    public function bonusAdd() {
        $user = $this->identity();
        $activity = Activity::model()->getOne($this->getParam('activity_id'));
        if ($activity){
            $awards = $activity->awards->toArray();
            //超过奖品数量判断
            foreach ($awards as $k=>$award){
                $award_issue_count = ActivitiesRecord::where('aword_id',$award['id'])->count();
                if ($award_issue_count >= $award['award_use_value']){
                    unset($awards[$k]);
                }
            }
            if ( count($awards) < 1){
                return $this->resultFail('Prizes have been drawn');
            }
            $award_bonus_index = array_rand($awards);
            ActivitiesRecordServer::server()->awardBonusWithId($awards[$award_bonus_index],$user->id);
            $user->bonus_count --;
            $user->save();
            return $this->resultSuccess($awards[$award_bonus_index]);
        }
    }
}
