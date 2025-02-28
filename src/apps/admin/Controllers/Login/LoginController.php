<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\Login;

use Admin\Controllers\BaseController;
use Admin\Rules\Login\LoginRule;
use Admin\Services\Login\LoginServer;
use Admin\Services\Staff\StaffServer;
use Admin\Services\Ticket\TicketServer;
use Common\Cookie\BindDing\BingDingCookie;
use Common\Utils\Data\HashHelper;
use Common\Utils\DingDing\DingData;
use Common\Utils\DingDing\DingUrl;
use Common\Utils\Email\EmailHelper;
use Common\Utils\Host\HostHelper;
use Common\Utils\LoginHelper;
use Illuminate\Support\Facades\Auth;

class LoginController extends BaseController
{
    public function pwdLogin(LoginRule $rule)
    {
        if (!$rule->validate(LoginRule::SCENARIO_PWD_LOGIN, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        $username = $this->request->get('username');
        $password = $this->request->get('password');
        $ip = HostHelper::getIp();
        $loginServer = new LoginServer();
        $loginServer->pwdLogin($username, $password, $ip);
        if ($loginServer->isError()) {
            return $this->result(400, $loginServer->msg, $loginServer->data);
        }
        return $this->resultSuccess($loginServer->data, $loginServer->msg);
    }

    public function dingLoginView()
    {
        $ding_url = DingUrl::getDingUrl();
        $goto = DingUrl::getGotoUrl();
        return $this->resultSuccess([
            'ding_appid' => config('config.ding_appid'),
            'ding_url' => $ding_url,
            'goto' => $goto,
        ]);
    }

    public function dingLoginBack()
    {
        $code = $this->request->get('code');
        $ip = HostHelper::getIp();
        $loginServer = new LoginServer();
        $loginServer->dingLogin($code, $ip);
        if ($loginServer->isError()) {
            return redirect('http://120.77.199.67/user/login?msg=' . $loginServer->getMsg() . '&to=bingDing');
            //return $this->sendError($loginServer->msg, 401, $loginServer->data);
        }
        return redirect('http://120.77.199.67/');
    }

    /**
     * @suppress PhanPluginUnreachableCode
     * @return \Illuminate\View\View
     */
    public function dingTest()
    {
        $dingUserInfo = ['openid' => '123', 'unionid' => 'qqq'];
        $bindingDingCookie = HashHelper::ticketEncrypt($dingUserInfo);
        BingDingCookie::set($bindingDingCookie);
        exit();

        $ticket = HashHelper::ticketEncrypt(['openid' => '123', 'unionid' => 'qqq']);
        var_dump(json_decode(HashHelper::ticketDecrypt($ticket), true));
        exit();

        //$ding_user_info =DingData::getDingUserInfoByCode('eea460cb1e8d32eabe8990b5817c9be9');
        $ding_user_info = DingData::getDingUserInfoByCode('698e91c18ff63acebcdfde91e6d8783f');
        var_dump($ding_user_info);
        exit();

        $title = 'test';
        $ding_url = DingUrl::getDingUrl();
        $goto = DingUrl::getGotoUrl();
        return view('login.dinglogin', compact('title', 'ding_url', 'goto'));
    }

    public function logout()
    {
        $staff = StaffServer::findOneById(LoginHelper::$modelUser['id']);
        if ($staff) {
            TicketServer::server()->logoutByStaff($staff);
        }
        return $this->resultSuccess();
    }

    public function info()
    {
        try {
            if ($userInfo = LoginHelper::$modelUser) {
                if ($tip = array_get($userInfo, 'tip')) {
                    return $this->result(401, $tip);
                }
                $userInfo['last_login_time'] = array_get($userInfo, 'last_login_time');
                $userInfo['time'] = date('Y-m-d H:i:s', $userInfo['time']);
                return $this->resultSuccess($userInfo);
            }
            return $this->result(401, '登录已失效,请重新登录-1');
        } catch (\Exception $e) {
            EmailHelper::sendException($e);
            return $this->result(401, '登录已失效,请重新登录-2');
        }

    }

    public function test()
    {
        return $this->resultSuccess([
            'id' => LoginHelper::getAdminId(),
            'username' => LoginHelper::$modelUser['username'],
        ], '用户信息');
        /*
        return $this->resultSuccess([
            'id' => Auth::guard('admin')->id(),
            'username' => Auth::guard('admin')->user()->username,
        ], '用户信息');
        */
    }

    public function loginTest()
    {
        $token = Auth::guard('admin')->attempt($this->request->only('username', 'password'));
        if (!$token) {
            return $this->result(400, '账号密码错误');
        }
        return $this->resultSuccess([
            'token' => $token
        ]);
    }

}
