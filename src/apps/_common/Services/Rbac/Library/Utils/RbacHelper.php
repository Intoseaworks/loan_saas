<?php

namespace Common\Services\Rbac\Library\Utils;

use Common\Services\Rbac\Library\Contracts\Config;
use Illuminate\Support\Facades\Artisan;

class RbacHelper
{
    /**
     * @var string
     */
    const USER_MENUS = '/api/rbac/menu/user-menus';

    /**
     * @var string
     */
    const SYNC_MENU = '/api/rbac/menu/sync-menu';

    /**
     * @var string
     */
    const MENU_LIST = '/api/rbac/menu/menu-list';

    /**
     * @param $path
     * @return array
     */
    public static function loadRouteFile($path)
    {
        $routeFiles = [];
        $files = glob($path);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $temp = static::loadRouteFile($file . '/*');
                if ($temp) {
                    array_push($routeFiles, ...$temp);
                }
            }
            if (is_file($file) && pathinfo($file)['extension'] == 'php') {
                $routeFiles[] = $file;
            }
        }

        return $routeFiles;
    }

    /**
     * 获取用户菜单
     *
     * @return array
     */
    public static function userMenu()
    {
        $config = app(Config::class);
        $uid = $config->getUserId();

        $data = [
            'referer' => $config->getProjectName(),
            'uid' => $uid,
            'guard_name' => config('auth.defaults.guard'),
        ];

        $data['sign'] = $config->getSign($data);
        $url = rtrim($config->getDomain(), '/') . static::USER_MENUS;

        $result = static::curlPost($url, $data);

        if (isset($result['code']) && $result['code'] == 18000) {
            return $result['data'];
        }

        return [];
    }

    /**
     * 获取全部菜单
     *
     * @return array
     */
    public static function menuList()
    {
        $config = app(Config::class);
        $uid = $config->getUserId();

        $data = [
            'referer' => $config->getProjectName(),
            'uid' => $uid,
            'guard_name' => config('auth.defaults.guard'),
        ];

        $data['sign'] = $config->getSign($data);
        $url = rtrim($config->getDomain(), '/') . static::MENU_LIST;

        $result = static::curlPost($url, $data);

        if (isset($result['code']) && $result['code'] == 18000) {
            return $result['data'];
        }

        return [];
    }

    /**
     * 同步菜单
     *
     * @param $menus
     * @return bool
     */
    public static function syncMenu($menus)
    {
        $config = app(Config::class);

        $data = [
            'referer' => $config->getProjectName(),
            'menus' => $menus,
            'guard_name' => config('auth.defaults.guard'),
        ];

        $data['sign'] = $config->getSign($data);
        $url = rtrim($config->getDomain(), '/') . static::SYNC_MENU;

        $result = static::curlPost($url, $data);

        if (isset($result['code']) && $result['code'] == 18000) {
            return true;
        }

        return $result['msg'];
    }

    /**
     * 同步接口
     *
     * @param $guard
     * @return bool
     */
    public static function syncPermission($guard)
    {
        //抑制输出
        ob_start();
        Artisan::call('rbac:permission:push', ['module' => $guard]);
        ob_end_clean();
        return true;
    }

    /**
     * @param $url
     * @param $params
     * @return bool|string
     */
    public static function curlPost($url, $params)
    {
        $client = new \GuzzleHttp\Client();
        $options = [
            'form_params' => $params,
        ];
        if (strpos($url, 'https://') === 0) {
            $options['verify'] = false;
        }
        try {
            $res = $client->post($url, $options);
        } catch (\Exception $e) {
            return false;
        }
        if ($res->getStatusCode() != 200) {
            return false;
        }
        return json_decode((string)$res->getBody(), true);
    }

}
