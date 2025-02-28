<?php

namespace Common\Services\Rbac\Controllers;

use Common\Services\Rbac\Models\Menu;
use Common\Services\Rbac\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class MenuController extends BaseController
{
    /**
     * @var Menu
     */
    private $menu;

    /**
     * MenuController constructor.
     * @param Menu $menu
     * @param Request $request
     */
    public function __construct(Menu $menu, Request $request)
    {
        $this->menu = $menu;
        $this->request = $request;
    }

    /**
     * 获取目录列表
     */
    public function index()
    {
        $operation = (bool)$this->request->input('operation', false);
        return $this->resultSuccess($this->menu->formatList($operation));
    }

    /**
     * 通过权限获取用户对应菜单
     *
     * @return array
     */
    public function menuList()
    {
        $cookieId = $this->request->cookie('tickesst_saas');
        if($cookieId){
            $key = md5($cookieId);
            return Cache::remember($key, 60, function(){
                return $this->resultSuccess($this->menu->findUserMenuByRole());
            });
        }
        //获取用户
        return $this->resultSuccess($this->menu->findUserMenuByRole());
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function syncMenu()
    {
        $menus = $this->request->input('menus');
        if (!$menus) {
            return $this->resultFail('菜单不存在');
        }
        !is_array($menus) && $menus = json_decode($menus, true);
        if (!$menus) {
            return $this->resultFail('菜单格式不正确');
        }
        //同步菜单
        $this->menu->syncMenu($menus);
        //同步接口
        ob_start();
        Artisan::call('rbac:permission:push', [
            'module' => Helper::getGuardName(),
        ]);
        ob_end_clean();
        return $this->resultSuccess();
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function syncSubProjectMenu()
    {
        $this->menu->syncSubProjectMenu();
        return $this->resultSuccess();
    }
}
