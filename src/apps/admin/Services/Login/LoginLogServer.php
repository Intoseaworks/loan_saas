<?php
/**
 * Created by PhpStorm.
 * User: xul
 * Date: 2018/8/1
 * Time: 21:08
 */

namespace Admin\Services\Login;

use Admin\Models\Login\LoginLog;
use Admin\Services\BaseService;

class LoginLogServer extends BaseService
{
    /**
     * @param $staff
     * @param $ip
     * @param $login_type
     * @return mixed
     */
    public function addOne($staff, $ip, $login_type)
    {
        $data = [
            'staff_id' => $staff->id,
            'merchant_id' => $staff->merchant_id,
            'ip' => $ip,
            //'client_url' => $redirect_uri ?? '',
            'client_url' => '',
            'login_type' => $login_type,
        ];
        return LoginLog::model()->addOne($data);
    }
}
