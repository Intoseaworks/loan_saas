<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\Login;

use Api\Services\BaseService;

class PwdServer extends BaseService {

    public static function buildPwdMd5($telephone, $pwd) {
        return md5($telephone . "#$#" . $pwd);
    }

}
