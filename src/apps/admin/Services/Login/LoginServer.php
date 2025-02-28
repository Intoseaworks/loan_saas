<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Login;

use Admin\Models\Login\LoginLog;
use Admin\Models\Staff\Staff;
use Admin\Services\BaseService;
use Admin\Services\Staff\StaffServer;
use Admin\Services\Ticket\TicketServer;
use Common\Cookie\BindDing\BingDingCookie;
use Common\Redis\Staff\StaffIdRedis;
use Common\Utils\Data\HashHelper;
use Common\Utils\DingDing\DingData;
use Common\Utils\MerchantHelper;

class LoginServer extends BaseService
{
    protected $loginSuccess = 'loginSuccess';//登录成功

    public function pwdLogin($username, $password, $ip)
    {
        /** 判断是否已登录 */
        if (LoginCheckServer::server()->hasLogin()) {
            return $this->outputSuccess('已登录', ['status' => $this->loginSuccess]);
        }
        $staff = Staff::model()->getOneByUsername($username);
        if (!$staff) {
            return $this->outputError('账号不存在，请重新填写');
        }
        // 在使用config前，需要设置根据用户设置 merchant_id
        MerchantHelper::setMerchantId($staff->merchant_id);
        /** 判断ip限制 */
        if (!LoginCheckServer::server()->checkIpFilter($ip)) {
            return $this->outputError('账号无权限登入系统');
        }
        if (!$ticket = TicketServer::server()->buildJwtTicket($username, $password)) {
            return $this->outputError('密码错误，忘记密码? 请与超级管理员联系');
        }
        if (!$staff->canLogin()) {
            return $this->outputError('账号无权限登入系统');
        }
        /** 写入token，多会话登录限制 */
        $this->writeToken($staff->id, $ticket);
        //更新数据库登录ip时间
        StaffServer::UpdateLoginInfo($staff->id, $ip);
        //写入日志
        LoginLogServer::server()->addOne($staff, $ip, LoginLog::LOGIN_TYPE_PWD);

        /** 绑定账密输入前扫码钉钉 设置缓存 */
        if ($bindingDing = BingDingCookie::get()) {
            //处理账密登录后绑定钉钉逻辑ticketDecrypt
            $dingUserInfo = json_decode(HashHelper::ticketDecrypt($bindingDing), true);
            $openid = array_get($dingUserInfo, 'openid');
            $unionid = array_get($dingUserInfo, 'unionid');
            StaffServer::saveDingId($staff->id, $openid, $unionid);
        }
        return $this->outputSuccess('欢迎登录后台管理系统', ['status' => $this->loginSuccess]);

    }

    public function dingLogin($code, $ip)
    {
        /** 判断ip限制 */
        if (!LoginCheckServer::server()->checkIpFilter($ip)) {
            return $this->outputError('账号无权限登入系统');
        }
        /** 判断是否已登录 */
        if (LoginCheckServer::server()->hasLogin()) {
            return $this->outputSuccess('已登录', ['status' => $this->loginSuccess]);
        }
        $dingUserInfo = DingData::getDingUserInfoByCode($code);
        if (!$dingUserInfo) {
            return $this->outputError('账号不存在，请重新填写');
        }
        $staff = Staff::model()->getOneByDingOpenId($dingUserInfo['user_info']['openid']);
        if (!$staff) {
            $bindingDingCookie = HashHelper::ticketEncrypt($dingUserInfo['user_info']);
            BingDingCookie::set($bindingDingCookie);
            return $this->outputError('输入账密绑定钉钉登录');
        }
        if ($staff->status != Staff::STATUS_NORMAL) {
            return $this->outputError('账号无权限登入系统');
        }
        /** 生成ticket */
        $ticket = TicketServer::server()->buildJwtTicketByStaff($staff);
        /** 多会话登录限制 */
        $this->writeToken($staff->id, $ticket);
        //更新数据库登录ip时间
        StaffServer::UpdateLoginInfo($staff->id, $ip);
        //写入日志
        LoginLogServer::server()->addOne($staff, $ip, LoginLog::LOGIN_TYPE_DING);
        return $this->outputSuccess('欢迎登录后台管理系统', ['status' => $this->loginSuccess]);
    }

    public function writeToken($staffId, $ticket)
    {
        /** 多会话登录限制 */
        if ($oldTicket = StaffIdRedis::redis()->get($staffId)) {
            if (LoginCheckServer::server()->onlyLoginOn()) {
                //清除旧登录
                TicketServer::server()->addBlack($oldTicket);
                //redis 设置
                TicketServer::server()->setRedis($staffId, $ticket);
            } else {
                $ticket = $oldTicket;
            }
        } else {
            //redis 设置
            TicketServer::server()->setRedis($staffId, $ticket);
        }
        //cookie 设置
        TicketServer::server()->setCookie($ticket);
    }
}
