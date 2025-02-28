<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Ticket;

use Admin\Models\Staff\Staff;
use Admin\Services\BaseService;
use Common\Cookie\Ticket\TicketCookie;
use Common\Models\Config\Config;
use Common\Redis\Staff\StaffIdRedis;
use Common\Redis\Ticket\TicketRedis;
use Common\Utils\Email\EmailHelper;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class TicketServer extends BaseService
{
    /**
     * 验证登录 & 生成ticket
     * @param $username
     * @param $password
     * @return mixed
     */
    public function buildJwtTicket($username, $password)
    {
        $this->setLoginExp();
        return Auth::guard('admin')->attempt(['username' => $username, 'password' => $password]);
    }

    /**
     * 根据staff model 生成ticket
     * @param $staff
     * @return mixed
     */
    public function buildJwtTicketByStaff($staff)
    {
        $this->setLoginExp();
        return Auth::guard('admin')->login($staff);
    }

    /**
     * 保存ticket到redis
     * @param $staffId
     * @param $ticket
     */
    public function setRedis($staffId, $ticket)
    {
        $expireTime = $this->setLoginExp();
        StaffIdRedis::redis()->set($staffId, $ticket, $expireTime);
    }

    public function setCookie($ticket)
    {
        TicketCookie::set($ticket);
    }

    public function setLoginExp()
    {
        $expireMinute = Config::model()->getLoginExpireHours() * 60;
        app('tymon.jwt.claim.factory')->setTTL($expireMinute);

        $expireSec = $expireMinute * 60;

        return $expireSec;
    }

    /**
     * 退出Staff账号
     * @param Staff $staff
     */
    public function logoutByStaff(Staff $staff)
    {
        TicketCookie::del();
        TicketServer::delUserTicket($staff->id);
    }

    /**
     * 清除ticket
     */
    public function logoutByTicket()
    {
        if ($ticket = TicketCookie::get()) {
            TicketRedis::redis()->del($ticket);
            TicketCookie::del();
        }
    }

    /**
     * 挤号踢出
     * 将之前的token加入黑名单
     * @param $ticket
     * @return mixed
     */
    public function addBlack($ticket)
    {
        try {
            $guard = JWTAuth::setToken($ticket);
            if ($guard->check()) {
                $guard->invalidate();
            }
        } catch (\Exception $e) {
            EmailHelper::sendException($e, 'kickUserTicket');
            return false;
        }
        return true;
    }

    /**
     * 删除用户登录信息
     * @param $id
     * @return mixed
     */
    public static function delUserTicket($id)
    {
        if ($ticket = StaffIdRedis::redis()->get($id)) {
            TicketRedis::redis()->del($ticket);
        }
        return StaffIdRedis::redis()->del($id);
    }
}
