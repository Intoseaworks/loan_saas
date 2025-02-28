<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\User;

use Admin\Models\BankCard\BankCard;
use Admin\Models\User\User;
use Admin\Models\User\UserAuth;

class UserAuthServer extends \Common\Services\User\UserAuthServer
{
    public function getAuthList(User $user)
    {
        $authList = [];
        //@phan-suppress-next-line PhanNonClassMethodCall
        $userAuths = array_pluck($user->userAuths->toArray(), 'type', 'type')?:[];
        foreach (UserAuth::TYPE as $key => $value) {
            //隐藏用户部分认证信息
            $needHiddens = ['aadhaarKYC认证','aadhaar认证','Pancard','选民证认证','护照认证','扩展信息'];
            if ( in_array($value,$needHiddens) ) {
                continue;
            }
            $authList[] = [
                'status' => array_get($userAuths, $key)?1:0,
                'name' => t($value, 'auth'),
            ];
        }
        return $authList;
    }

    /**
     * 清理用户银行卡信息
     * @param $userId
     * @return bool
     */
    public function clearBankCard($userId)
    {
        //清理银行卡信息
        BankCard::clearStatusSystem($userId);
        //清除认证状态
        UserAuth::model()->clearAuth($userId, UserAuth::TYPE_BANKCARD);

        return true;
    }
}
