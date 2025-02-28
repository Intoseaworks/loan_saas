<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 15:47
 */

namespace Api\Services\User;

use Admin\Models\User\UserBlack;
use Api\Models\User\User;
use Api\Services\BaseService;
use Api\Services\Login\LoginServer;
use Auth;
use Common\Exceptions\ApiException;
use Common\Models\Config\Config;
use Common\Models\Login\UserLoginLog;
use Common\Models\Order\Order;
use Common\Utils\MerchantHelper;
use Exception;
use Common\Models\Collection\CollectionContact;
use Common\Models\Collection\Collection;

class UserCheckServer extends BaseService {

    /**
     * 检查用户是否异常
     * 1、黑名单
     * 2、..
     * @param $telephone
     * @throws ApiException
     */
    public function checkUser($telephone) {
        if (!UserBlack::model()->canLogin($telephone)) {
            if (Auth::user()) {
                /** 如果已登录退出登录 */
                LoginServer::server()->logout();
            }
            return $this->outputException('暂无登录资格');
        }
    }

    public function canRegister() {
        if ($this->reachMaxRegister()) {
            $this->outputException('In order to provide better service for you, we are upgrading the system. We will complete the upgrade as soon as possible. Please try again later.');
        }
    }

    /**
     * 日注册数策略拦截
     * 配置值为 0，则是可申请任意数量，不拦截
     * @return bool
     * @throws Exception
     */
    public function reachMaxRegister() {
        $dailyRegisterCount = UserServer::server()->getDailyRegisterCount();
        $maxRegisterNum = Config::model()->getDailyRegisterUserMax();

        return $dailyRegisterCount >= $maxRegisterNum;
    }

    /**
     * 查询证件号是否注册过，返回手机号
     * @param type $cardNo
     * @return boolean
     */
    public function verifyCardReg($cardNo, $userId) {
        $res = User::model()->where('id_card_no', $cardNo)->where('id', "<>", $userId)->orderByDesc("id")->get();
        if ($res) {
            return $res;
        }
        return false;
    }

    public function checkCard($cardNo, $userId) {
        $regTels = $this->verifyCardReg($cardNo, $userId);
        $currentUser = Auth::user();
        $actionStep = $actionUser = NULL;
        # 已注册过
        if ($regTels !== false) {
            foreach ($regTels as $user) {
                # 是否是黑名单
                if (UserBlack::model()->isActive()->whereTelephone($user->telephone)->exists()) {
                    //return $this->outputException('已注册，请用原账号登录（认证失败），如有疑问请联系客服。');
                }
                if ($user->getIsCompleted()) {
                    $order = $user->order;
                    # 逾期订单
                    if ($order->status == Order::STATUS_OVERDUE) {
                        $actionStep = 1;
                        $actionUser = $user;
                    }
                    # 未完成订单
                    if (in_array($order->status, Order::STATUS_NOT_COMPLETE)) {
                        if ($actionStep > 2 || $actionStep === NULL) {
                            $actionStep = 2;
                            $actionUser = $user;
                        }
                    }
                    # 拒绝 | 结清
                    if (in_array($order->status, Order::APPROVAL_REJECT_STATUS) || in_array($order->status, Order::FINISH_STATUS)) {
                        if (Order::model()->whereIn("status", Order::FINISH_STATUS)->where("user_id", $user->id)->exists()) {
                            if ($actionStep === NULL) {
                                $actionStep = 3;
                                $actionUser = $user;
                            }
                        }
                    }

                    if (in_array($order->status, array_merge(Order::APPROVAL_REJECT_STATUS, [Order::STATUS_USER_CANCEL, Order::STATUS_SYSTEM_CANCEL, Order::STATUS_MANUAL_CANCEL]))) {
                        if (!Order::model()->whereIn("status", Order::FINISH_STATUS)->where("user_id", $user->id)->exists()) {
                            if ($actionStep === NULL) {
                                $actionStep = 4;
                                $actionUser = $user;
                            }
                        }
                    }
                } else {
                    UserBlack::model()->add([
                        "merchant_id" => MerchantHelper::helper()->getMerchantId(),
                        'telephone' => $user->telephone,
                        'type' => UserBlack::TPYE_CANNOT_LOGIN,
                        'black_time' => date("Y-m-d H:i:s"),
                        'remark' => "证件号再次注册入黑003 ({$userId})",
                            ], 90);
                    //return true;
                }
            }
            switch ($actionStep) {
                case 1:
                    $collection = Collection::model()->where("order_id", $actionUser->order->id)->first();
                    if($collection){
                        $userContactData = [
                            'merchant_id' => $actionUser->merchant_id,
                            'order_id' => $actionUser->order->id,
                            'user_id' => $actionUser->id,
                            'collection_id' => $collection->id,
                            'type' => CollectionContact::TYPE_USER_CONTACT,
                            'fullname' => $currentUser->fullname,
                            'contact' => $currentUser->telephone,
                            'relation' => \Common\Models\User\UserContact::RELATION_OTHER,
                        ];
                        CollectionContact::model()->updateOrCreateModel($userContactData, $userContactData);
                    }
                    return $this->outputException('有未结清贷款，请用原账号登录，如有疑问请联系客服。');
                case 2:
                    return $this->outputException('已注册，请用原账号登录（认证失败），如有疑问请联系客服。');
                case 3:
                    $lastLoginTime = UserLoginLog::model()->newQuery()->where("user_id", $actionUser->id)->max("created_at");
                    # 30天未登录
                    if ($lastLoginTime < date("Y-m-d H:i:s", time() - 86400 * 30)) {
                        UserBlack::model()->add([
                            "merchant_id" => MerchantHelper::helper()->getMerchantId(),
                            'telephone' => $actionUser->telephone,
                            'type' => UserBlack::TPYE_CANNOT_LOGIN,
                            'black_time' => date("Y-m-d H:i:s"),
                            'remark' => "证件号再次注册入黑001 ({$userId})",
                                ], 90);
                    } else {
                        return $this->outputException('已注册，请用原账号登录（认证失败），如有疑问请联系客服。');
                    }
                    break;
                case 4:
                    #历史无成功放款记录
                    UserBlack::model()->add([
                        "merchant_id" => MerchantHelper::helper()->getMerchantId(),
                        'telephone' => $actionUser->telephone,
                        'type' => UserBlack::TPYE_CANNOT_LOGIN,
                        'black_time' => date("Y-m-d H:i:s"),
                        'remark' => "证件号再次注册入黑002 ({$userId})",
                            ], 90);
                    break;
            }
            return null;
        }
    }

}
