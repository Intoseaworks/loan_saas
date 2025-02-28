<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Common\Services\Crm;

use Common\Services\BaseService;
use Common\Models\User\User;
use Common\Models\Crm\CrmWhiteList;

class WhiteListServer extends BaseService {

    public function check(User $user) {
        $isWhitelist = false;
        # 手机号匹配
        $isWhitelist = CrmWhiteList::model()->isActive()->where("telephone", $user->telephone)->exists();
        \Illuminate\Support\Facades\Log::info(["isWL" => $isWhitelist, "telephone" => $user->telephone]);
        if($isWhitelist){
            return true;
        }
        return false;
    }

}
