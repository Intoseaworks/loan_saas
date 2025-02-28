<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Login;

use Admin\Models\Staff\Staff;
use Admin\Services\BaseService;
use Admin\Services\Ticket\TicketServer;
use Common\Models\Config\Config;
use Illuminate\Support\Facades\Auth;

class LoginCheckServer extends BaseService
{
    /**
     * @return bool
     */
    public function hasLogin()
    {
        if (Auth::guard('admin')->guest()) {
            TicketServer::server()->logoutByTicket();
            return false;
        }
        return true;
    }

    /**
     * 判断是否多地登录限制
     *
     * @return bool
     */
    public function onlyLoginOn()
    {
        return Config::getValueByKey(Config::KEY_LOGIN_ONLY_LOGIN_ON);
    }

    /**
     * ip白名单
     *
     * @param $ip
     * @return bool
     */
    public function checkIpFilter($ip)
    {
        if(!Config::model()->getLoginIpFilterOn()){
            return true;
        }
        $loginIpFilter = (array)Config::model()->getLoginIpFilter();
        if(in_array($ip, $loginIpFilter)){
            return true;
        }
        return false;
    }

    /**
     * 判断用户是否需钉钉登录
     *
     * @param Staff $staff
     * @return mixed
     */
    public function checkNeedDingLogin(Staff $staff)
    {
        //优先判断用户是否需钉钉登录
        if ($staff->is_need_ding_login == Staff::IS_NEED_DING_LOGIN_NEED) {
            return true;
        }
        //其次判断公司是否需钉钉登录
        if (Config::getValueByKey(Config::KEY_DING_LOGIN_ON)) {
            return true;
        }
        return false;
    }

}
