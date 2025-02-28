<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\User;

use Api\Models\User\UserAuth;

class UserAuthServer extends \Common\Services\User\UserAuthServer
{
    public function getAuth($userId, $type)
    {
        $auth = UserAuth::model()->getAuth($userId, $type);
        if(!$auth){
            return UserAuth::AUTH_STATUS_NOT;
        }
        if($auth->auth_status != UserAuth::AUTH_STATUS_SUCCESS){
            return $auth->auth_status;
        }
        //TODO 过期判断
        return $auth->auth_status;
    }

    /**
     * 验证用户认证项
     * @param $user
     * @param $type
     *
     * @return bool
     */
    public function validAuth($user, $type = null)
    {
        if (is_null($type)) {
            $type = array_keys(UserAuth::TYPE);
        }
        $type = (array)$type;

        //@phan-suppress-next-line PhanNonClassMethodCall
        $userAuth = $user->userAuths->whereIn('type', $type)->where('auth_status', UserAuth::AUTH_STATUS_SUCCESS);

        if (count($type) != $userAuth->count()) {
            return false;
        }

        return true;
    }
}
