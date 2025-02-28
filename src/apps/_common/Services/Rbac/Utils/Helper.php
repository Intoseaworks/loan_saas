<?php
/**
 * Created by PhpStorm.
 * User: Summer
 * Date: 2018/8/9
 * Time: 16:15
 */

namespace Common\Services\Rbac\Utils;

use Common\Models\Staff\Staff;
use Common\Services\Rbac\Models\Menu;
use Common\Services\Rbac\Models\Role;
use Common\Utils\LoginHelper;

class Helper
{
    /**
     * @var Staff
     */
    public static $user = null;

    /**
     * 清理无限极分类里面的空column
     *
     * @param $data
     */
    public static function cleanNullChildren(&$data, $column = 'children')
    {
        foreach ($data as &$item) {
            if (isset($item[$column]) && is_array($item[$column]) && !empty($item[$column])) {
                static::cleanNullChildren($item[$column], $column);
            }
            if (empty($item[$column])) {
                unset($item[$column]);
            }
        }
    }

    /**
     * @param array $data
     * @param string $sortBy
     * @param string $children
     */
    public static function sortRecursive(&$data, $sortBy, $sort = SORT_ASC, $children = 'children')
    {
        //排序
        usort($data, function ($a, $b) use ($sortBy, $sort) {
            if ($sort == SORT_DESC) {
                return ($a[$sortBy] > $b[$sortBy]) ? -1 : 1;
            }
            return ($a[$sortBy] < $b[$sortBy]) ? -1 : 1;
        });

        foreach ($data as &$item) {
            if (isset($item[$children]) && is_array($item[$children]) && !empty($item[$children])) {
                static::sortRecursive($item[$children], $sortBy);
            }
        }
    }

    /**
     * 获取数据库连接
     *
     * @return bool|mixed
     */
    public static function getConnection()
    {
        return static::getEnvConfig()['connection'] ?? abort(500, '数据库信息未配置');
    }

    /**
     * 获取用户应用配置信息
     *
     * @return mixed
     */
    public static function getEnvConfig()
    {
        return config('permission.config');
    }

    /**
     * 获取数据库名称
     *
     * @return string
     */
    public static function getDatabaseName($connection)
    {
        return config("database.connections.{$connection}.database");
    }

    /**
     * @param null $uid
     * @return bool
     */
    public static function isSuperAdmin()
    {
        $userRoleIds = static::getUser()
            ->roles
            ->pluck('id');

        $superRole = Role::getSuperRole();

        if (!$superRole) {
            return false;
        }

        //拥有超级角色
        if ($userRoleIds->contains($superRole->id)) {
            return true;
        }

        if (in_array(static::getUser()->id, static::getSuperArr())) {
            return true;
        }

        return false;

    }

    /**
     * 获取当前登录用户
     *
     * @return Staff|null
     */
    public static function getUser()
    {
        if (is_null(static::$user)) {
            $userId = LoginHelper::getAdminId();
            /** @var Staff $user */
            $user = app('rbacUser');
            $user->setAttribute('id', $userId);
            $user->guard_name = static::getGuardName();
            static::$user = $user;
            return $user;
        }

        return static::$user;
    }

    /**
     * 获取guard
     *
     * @return string
     */
    public static function getGuardName()
    {
        $guard = app('request')->input('guard_name', 'admin');
        return $guard;
    }

    /**
     * @return array
     */
    public static function getSuperArr()
    {
        return static::getEnvConfig()['super'] ?? [];
    }

    /**
     * @return string
     */
    public static function getRoutePath()
    {
        $request = app('request');
        $route = $request->route();
        $path = $request->path();
        $method = $request->method();
        if (isset($route[1]['symbol'])) {
            $symbol = $route[1]['symbol'];
            return $method . '/' . app()->router->irregularityRoutes[$symbol] ?? null;
        } else {
            return $method . '/' . $path;
        }
    }

    /**
     * 用户AppKey鉴权获取uid
     *
     * @return array|null|string
     */
    public static function getAppKeyUserId()
    {
        return app('request')->input('uid');
    }

    /**
     * 查找两个数据的差集,找出删除、更新、增加数据.(menu和permission使用)
     *
     * @param $old
     * @param $new
     * @return array
     */
    public static function arrayDiff($old, $new)
    {
        $map = function ($arr) {
            $new = [];
            foreach ($arr as $k => $item) {
                $new[$k] = json_encode($item, JSON_UNESCAPED_UNICODE);
            }
            return $new;
        };

        $oldTemp = $map($old);
        $newTemp = $map($new);

        //删除获取更新
        $diff1 = array_diff_assoc($oldTemp, $newTemp);
        //增加获取更新
        $diff2 = array_diff_assoc($newTemp, $oldTemp);

        $delete = [];
        $add = [];
        $update = [];
        foreach ($diff1 as $key => $diff) {
            //判断key是否在$diff2中存在,存在代表更.不存在代表删除
            if (array_key_exists($key, $diff2)) {
                $update[$key] = $new[$key];
                unset($diff2[$key]);
            } else {
                $delete[$key] = $old[$key];
            }
        }

        foreach ($diff2 as $key => $diff) {
            $add[$key] = $new[$key];
        }

        return ['delete' => $delete, 'add' => $add, 'update' => $update];
    }

    /**
     * 判断rbac是否关闭 true关闭,false开启
     *
     * @return mixed
     */
    public static function isRbacClosed()
    {
        return static::getEnvConfig()['closed'];
    }

    /**
     * @return int
     */
    public static function menuType()
    {

        return Menu::MENU_TYPE_NORMAL;
    }
}
