<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Test;

use Admin\Models\User\User;
use Admin\Services\BaseService;
use Common\Models\Test\TestUser;
use Illuminate\Support\Facades\DB;

class TestUserServer extends BaseService
{
    public function clearUser($params)
    {
        $telephone = array_get($params, 'telephone');
        if (!$telephone) {
            return $this->outputException('请输入手机号');
        }
        if (!($user = User::model()->where('telephone', $telephone)->orWhere('id', $telephone)->first())) {
            return $this->outputException('用户不存在');
        }
        if(!TestUser::model()->isTestUser($user->id)){
            if (!in_array($telephone, [6366724685, 6366728985, 6364947869])) {
                return $this->outputException('非测试用户');
            }
            TestUser::model()->add($user);
        }
        $userId = $user->id;
        DB::beginTransaction();
        $user->telephone = $user->telephone.'-'.$userId;
        if($userInfo = $user->userInfo){
            $userInfo->pan_card_no = $userInfo->pan_card_no.'-'.$userId;
            $userInfo->aadhaar_card_no = $userInfo->aadhaar_card_no.'-'.$userId;
        }
        if(!$user->save() || ($userInfo && !$userInfo->save())){
            DB::rollBack();
        }
        if(!DB::update("UPDATE user_phone_hardware set advertising_id = CONCAT_WS('-', advertising_id, {$userId}) where user_id = {$userId}")){
            DB::rollBack();
        };
        DB::commit();
    }

}
