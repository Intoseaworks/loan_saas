<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-11-30
 * Time: 14:51
 */

namespace Common\Events\Login;


use Api\Models\User\User;
use Common\Events\Event;
use Jenssegers\Agent\Agent;

/**
 * Class LoginEvent
 * @package Common\Events\Login
 *
 * @phan-file-suppress PhanUndeclaredTypeProperty
 */
class LoginEvent extends Event
{
    /**
     * @var User 用户模型
     */
    protected $user;

    /**
     * @var Agent Agent对象
     */
    protected $agent;

    /**
     * @var string IP地址
     */
    protected $ip;

    /**
     * 登录时间戳
     */
    protected $datetime;
    
    protected $appVersion;

    /**
     * 实例化事件时传递这些信息
     * @param $user
     * @param $agent
     * @param $ip
     * @param $datetime
     */
    public function __construct($user, $agent, $ip, $datetime, $appVersion = '')
    {
        $this->user = $user;
        $this->agent = $agent;
        $this->ip = $ip;
        $this->datetime = $datetime;
        $this->appVersion = $appVersion;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getAgent()
    {
        return $this->agent;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function getDatetime()
    {
        return $this->datetime;
    }
    
    public function getAppVersion(){
        return $this->appVersion;
    }
}
