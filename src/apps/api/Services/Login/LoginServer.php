<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\Login;

use Admin\Services\Activity\ActivitiesRecordServer;
use Admin\Services\Activity\ActivityAwardServer;
use Admin\Services\Coupon\CouponReceiveServer;
use Admin\Services\Coupon\CouponTaskServer;
use Api\Models\Coupon\Coupon;
use Api\Rules\User\LoginRule;
use Api\Services\BaseService;
use Api\Services\Third\WhatsappServer;
use Api\Services\User\UserCheckServer;
use Carbon\Carbon;
use Common\Events\Login\LoginEvent;
use Common\Events\Risk\RiskDataSendEvent;
use Common\Events\User\UserSetClientIdEvent;
use Common\Exceptions\ApiException;
use Common\Jobs\Crm\CouponTaskIssueToRegisterUserJob;
use Common\Models\Channel\Channel;
use Common\Models\User\User;
use Common\Models\User\UserBlack;
use Common\Models\User\UserInviteCode;
use Common\Redis\CommonRedis;
use Common\Redis\RedisKey;
use Common\Utils\Host\HostHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Until;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;
use Yunhan\Utils\Env;

class LoginServer extends BaseService
{
    public function loginByAccessToken($accessToken)
    {
        return AccessTokenServer::server()->findIdentityByAccessToken($accessToken);
    }

    /**
     * 注册
     * @param $attributes
     * @return array
     * @throws ApiException
     */
    public function register($attributes)
    {
        $telephone = array_get($attributes, 'telephone');
        $channelCode = array_get($attributes, 'channel');
        $appVersion = '';
        UserCheckServer::server()->checkUser($telephone);
        UserCheckServer::server()->canRegister();

        # 注册黑名单判断
        if (!UserBlack::model()->canRegister($telephone)) {
            return $this->outputException('暂无登录资格');
        }

        # 渠道判断
        $channel = Channel::model()->getByCode($channelCode);
        if ($channel) {
            if ($channel->status != Channel::STATUS_NORMAL) {
                throw new ApiException('暂无注册资格');
            }
            $attributes['channel_id'] = $channel->id;
        }

        if ($appVersion = app(Request::class)->header('app-version')) {
            $attributes['app_version'] = $appVersion;
        }

        //邀请码使用
        $inviteCode = array_get($attributes, 'invite_code');
        if ($inviteCode){
            $inviteCode = UserInviteCode::model()->getUserByCode($inviteCode);
            if (!$inviteCode){
                return $this->outputException('invite code is invalid!');
            }
        }

        if (!$user = User::model(LoginRule::SCENARIO_LOGIN)->saveModel($attributes)) {
            throw new ApiException('用户注册失败');
        }

        //邀请码永不过期过期
        if ( $inviteCode && is_object($inviteCode) ){
            if (!$inviteCode->invited_user){
                $inviteCode->invited_user = $user->id;
                $inviteCode->save();
            }else{
                UserInviteCode::createModel(['invited_user'=>$user->id,'invite_code'=>$inviteCode->invite_code,'user_id'=>$inviteCode->user_id]);
            }
            //发放奖励
            ActivitiesRecordServer::server()->awardBonus(1,1,1,MerchantHelper::getMerchantId(),Carbon::now()->toDateTimeString(),$inviteCode->user_id);
//            $activityAwardConditions = [];
//            $activityAwardConditions[] = ['type','=',1];
//            $activityAwardConditions[] = ['status','=',1];
//            $activityAwardConditions[] = ['award_condition','=',1];
//            $activityAwardConditions[] = ['merchant_id','=',MerchantHelper::getMerchantId()];
//            $activityConditions = [];
//            $activityConditions[] = ['type','=',1];
//            $activityConditions[] = ['status','=',1];
//            $activityConditions[] = ['end_time','>',Carbon::now()->toDateTimeString()];
//            $activityConditions[] = ['merchant_id','=',MerchantHelper::getMerchantId()];
//            $awards = ActivityAwardServer::server()->getActivityAwards($activityAwardConditions,$activityConditions);
//            foreach ($awards as $award){
//                $params['coupon_id'] = $award->award_value;
//                $params['user_id'] = $inviteCode->user_id;
//                $params['coupon_task_id'] = 0;
//                $res = CouponReceiveServer::server()->addCouponReceive($params);
//                if ( $res->isSuccess() ){
//                    $paramsActivitiesRecord['record_id'] = $res->getData()->id;
//                    $paramsActivitiesRecord['user_id'] = $inviteCode->user_id;
//                    $paramsActivitiesRecord['status'] = 2;
//                    $paramsActivitiesRecord['activity_id'] = $award->activities[0]->id;
//                    $paramsActivitiesRecord['aword_id'] = $award->id;
//                    $paramsActivitiesRecord['award_condition'] = $award->award_condition;
////                    dd($paramsActivitiesRecord);
//                    ActivitiesRecordServer::server()->addCouponReceive($paramsActivitiesRecord);
//                }
//            }
        }

        # 发送what's 验证请求
        if(Env::isProd()){
            WhatsappServer::server()->send($telephone);
        }
        /** 登录事件 */
        //@phan-suppress-next-line PhanUndeclaredClassMethod
        event(new LoginEvent($user, new Agent(), HostHelper::getIp(), $this->getDateTime(), $appVersion));
        // 风控数据上传
        event(new RiskDataSendEvent($user->id, RiskDataSendEvent::NODE_USER_REGISTER));
        //注册时发送优惠券和通知
        dispatch(new CouponTaskIssueToRegisterUserJob($user));
        $data = $this->buildAllToken($user->id);
        //判断是否有优惠券注册时发放
        $will_get_coupon = [];
        $whereCouponTasks = [];
        $whereCouponTasks[] = ['grant_type','=',1];
        $whereCouponTasks[] = ['status','=',1];
        $couponTasks = CouponTaskServer::server()->getCouponTasks($whereCouponTasks);
        foreach ($couponTasks as $couponTask){
            $coupon = Coupon::model()->getOne($couponTask->coupon_id);
            if ($coupon && $coupon->merchant_id == $user->merchant_id ){
              $will_get_coupon['coupon_type'] = $coupon->coupon_type;
              $will_get_coupon['end_time'] = Carbon::parse(date('Y-m-d'))->addDays($coupon->effectivedays)->toDateString();
              break;
            }
        }
        if (count($will_get_coupon)){
            $data['will_get_coupon'] = $will_get_coupon;
        }else{
            $data['will_get_coupon'] = '';
        }
        $user->bonus_count = 1;
        $user->save();
        return $data;
    }

    /**
     * 登录
     * @param User $user
     * @return array
     * @throws ApiException
     */
    public function login(User $user)
    {
        $appVersion = '';
        if ($user->app_version == '' && ($appVersion = app(Request::class)->header('app-version'))) {
            $user->app_version = $appVersion;
            $user->save();
        }
        UserCheckServer::server()->checkUser($user->telephone);
        /** 登录事件 */
        //@phan-suppress-next-line PhanUndeclaredClassMethod
        event(new LoginEvent($user, new Agent(), HostHelper::getIp(), $this->getDateTime(), $appVersion));

        return $this->buildAllToken($user->id);
    }

    /**
     * 变更来源渠道
     * @param User $user
     * @param $clientId
     * @return User
     */
    public function setUserClientId(User $user, $clientId)
    {
        if ($clientId == $user::CLIENT_H5) {
            return $user;
        }
        $res = event(new UserSetClientIdEvent($user, $clientId));

        return $res;
    }

    /**
     * 获取身份令牌
     * @param $userId
     * @return array
     */
    public function buildAllToken($userId)
    {
        $class = Until::getCurrentUserModel();
        $user = $class::find($userId);
        $accessToken = Auth::guard('api')->login($user);
        $uuid = app(Request::class)->header('device-uuid');
        $uuid && $this->updateRedisUuid($userId, $uuid);
        return [
            'client_id' => $user->client_id,
            'userId' => $userId,
            'token' => $accessToken,
            'refresh_token' => '',
        ];
    }

    public function refreshToken($token = '')
    {
        $accessToken = Auth::guard('api')->refresh();
        $userId = Auth::guard('api')->id();
        $redisUuid = $this->getRedisUuid($userId);
        $uuid = app(Request::class)->header('device-uuid');
        if ($userId && $redisUuid && $uuid && $redisUuid != $uuid) {
            Auth::guard('api')->logout();
            throw new ApiException(t('登录已过期，请重新登录'), 1403);
        }
        $uuid && $this->updateRedisUuid($userId, $uuid);
        return [
            'token' => $accessToken,
        ];
    }

    public function logout()
    {
        Auth::guard('api')->logout();
    }

    public function updateRedisToken($userId, $accessToken)
    {
        $key = RedisKey::PREFIX_APP_USER_TOKEN . $userId;
        $expireTime = config('jwt.refresh_ttl', 20160) * 60;
        CommonRedis::redis()->set($key, $accessToken, $expireTime);
    }

    public function getRedisToken($userId)
    {
        $key = RedisKey::PREFIX_APP_USER_TOKEN . $userId;
        return CommonRedis::redis()->get($key);
    }

    public function updateRedisUuid($userId, $uuid)
    {
        $key = RedisKey::PREFIX_APP_USER_UUID . $userId;
        $expireTime = config('jwt.refresh_ttl', 20160) * 60;
        CommonRedis::redis()->set($key, $uuid, $expireTime);
    }

    public function getRedisUuid($userId)
    {
        $key = RedisKey::PREFIX_APP_USER_UUID . $userId;
        return CommonRedis::redis()->get($key);
    }

    public static function buildPwdMd5($telephone, $pwd){
        return  md5($telephone . "#$#" . $pwd);
    }
}
