<?php

namespace Common\Listeners\Login;

use Common\Events\Login\LoginEvent;
use Common\Models\Login\UserLoginLog;
use Common\Utils\Host\HostHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Contracts\Queue\ShouldQueue;

class LoginListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function handle(LoginEvent $event)
    {
        $this->storeLog($event);
    }

    /**
     *
     * @param LoginEvent $event
     * @suppress PhanUndeclaredClassMethod
     */
    public function storeLog(LoginEvent $event)
    {
        MerchantHelper::clearMerchantId();

        //获取事件中保存的信息
        $user = $event->getUser();
        $agent = $event->getAgent();
        $ip = $event->getIp();
        $dataTime = $event->getDatetime();
        $appVersion = $event->getAppVersion();

        //登录信息
        $login_info = [
            'ip' => $ip,
            'user_id' => $user->id,
            'created_at' => $dataTime,
            'updated_at' => $dataTime,
            'app_id' => $user->app_id,
        ];
        
        if($appVersion){
            $login_info['app_version'] = $appVersion;
        }

        // zhuzhichao/ip-location-zh 包含的方法获取ip地理位置
        $addresses = HostHelper::getAddressByIp($ip);
        $login_info['address'] = $addresses;

        // jenssegers/agent 的方法来提取agent信息
        $login_info['device'] = $agent->device(); //设备名称
        $browser = $agent->browser();
        $login_info['browser'] = $browser . ' ' . $agent->version($browser); //浏览器
        $platform = $agent->platform();
        $login_info['platform'] = $platform . ' ' . $agent->version($platform); //操作系统
        $login_info['language'] = implode(',', $agent->languages()); //语言
        //设备类型
        if ($agent->isTablet()) {
            // 平板
            $login_info['device_type'] = 'tablet';
        } else {
            if ($agent->isMobile()) {
                // 便捷设备
                $login_info['device_type'] = 'mobile';
            } else {
                if ($agent->isRobot()) {
                    // 爬虫机器人
                    $login_info['device_type'] = 'robot';
                    $login_info['device'] = $agent->robot(); //机器人名称
                } else {
                    // 桌面设备
                    $login_info['device_type'] = 'desktop';
                }
            }
        }

        //插入到数据库
        UserLoginLog::query()->insert($login_info);
    }
}
