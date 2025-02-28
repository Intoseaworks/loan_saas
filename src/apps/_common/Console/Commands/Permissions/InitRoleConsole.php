<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/2/14
 * Time: 17:14
 */

namespace Common\Console\Commands\Permissions;

use Admin\Models\Staff\Staff;
use Common\Models\Config\Config;
use Common\Models\Merchant\Merchant;
use Common\Services\Rbac\Models\Role;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InitRoleConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:init-role {--merchantId=} {--initUsername=} {--initPassword=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化角色';

    public function handle()
    {
        $merchantId = $this->option('merchantId');
        $initUsername = $this->option('initUsername');
        $initPassword = $this->option('initPassword');

        if (isset($merchantId)) {
            $merchant = Merchant::model()->getNormalById($merchantId);
            if (!$merchant) {
                $this->error('指定商户不存在');
                die();
            }
            $this->initMerchantRole($merchant, $initUsername, $initPassword);
        } else {
            $merchants = Merchant::model()->getNormalAll();
            foreach ($merchants as $merchant) {
                $this->initMerchantRole($merchant, $initUsername, $initPassword);
            }
        }
    }

    public function initMerchantRole($merchant, $initUsername = null, $initPassword = null)
    {
        // 在command模式下,MerchantHelper::$merchantId 为null,手动赋值.避免model merchantScope不起作用
        MerchantHelper::setMerchantId($merchant->id);
        $this->initRole($merchant, $initUsername, $initPassword);
    }

    protected function initRole($merchant, $initUsername = null, $initPassword = null)
    {
        $merchantId = $merchant->id;
        if (!isset($initUsername)) {
            $initUsername = 'admin';
        }
        if (!isset($initPassword)) {
            $initPassword = '123456';
        }

        $path = storage_path('permissions/init-roles.php');
        if (!file_exists($path)) {
            $this->error("初始化文件:`$path`文件不存在!");
            return;
        }
        $roles = require $path;

        $superStaff = $merchant->superAdmin;
        if (!$this->isInitialized() || !isset($superStaff)) {
            /** @var Staff $superStaff */
            $superStaff = $superStaff ?? new Staff();
            $superStaff->username = $initUsername;
            $superStaff->nickname = $initUsername;
            $superStaff->status = Staff::STATUS_NORMAL;
            $superStaff->password = Staff::passwordHash(trim($initPassword));
            $superStaff->merchant_id = $merchantId;
            $superStaff->created_at = date('Y-m-d H:i:s');
            $superStaff->updated_at = date('Y-m-d H:i:s');
            $superStaff->last_update_pwd_time = date('Y-m-d H:i:s');
            $superStaff->is_super = Staff::IS_SUPER_YES;
            $superStaff->save();
        }

        $delMenus = [];
        $addMenus = [];
        $menuHistory = $this->getHistoryMenu();
        // 初始化角色
        foreach ($roles as $role) {
            // 超级管理员
            if (isset($role['is_super']) && $role['is_super']) {
                // 这里不能使用 updateOrCreate. role model 定义了fillable字段,id不在fillable数组内会被过滤掉
                /** @var Role $model */
                $model = Role::firstOrNew([
                    'merchant_id' => $superStaff->merchant_id,
                    'is_super' => Role::IS_SUPER_YES,
                ]);
                $model->name = $role['name'];
                $model->guard_name = $role['guard_name'];
                $model->merchant_id = $superStaff->merchant_id;
                $model->save();
                continue;
            }

            /** @var Role $row */
            $row = Role::where('name', $role['name'])->first();
            if ($row) {
                if ($this->isInitialized()) {
                    $oldMenus = $row->menus
                        ->pluck('path')
                        ->toArray();
                    $newMenus = $role['menus'];
                    if (isset($menuHistory[$role['name']])) {
                        // 新增.只新增没有记录的,因为后台管理员可能会删除该角色一些权限
                        $role['menus'] = array_diff(array_diff($newMenus, $oldMenus), $menuHistory[$role['name']]);
                        // 删除.只删除有过记录的.因为后台管理员可能会给该角色赋予其他的权限
                        $delMenu = array_intersect($menuHistory[$role['name']], array_diff($oldMenus, $newMenus));
                    } else {
                        $role['menus'] = array_diff($newMenus, $oldMenus);
                        $delMenu = array_diff($oldMenus, $newMenus);
                    }

                    if ($delMenu) {
                        $row->menus()->detach($delMenu);
                        $delMenus[$role['name']] ?? $delMenus[$role['name']] = [];
                        array_push($delMenus[$role['name']], ...$delMenu);
                    }
                } else {
                    // 清除菜单
                    $row->menus()->detach();
                }
            }

            // 保存角色
            $this->saveRole($role);

            if ($role['menus']) {
                $addMenus[$role['name']] ?? $addMenus[$role['name']] = [];
                array_push($addMenus[$role['name']], ...$role['menus']);
            }
        }

        // 注意这里的 $superStaff 要是 `Admin\Models\Staff\Staff`
        $superRoleModel = Role::where('merchant_id', $superStaff->merchant_id)->first();
        if (isset($superStaff)) {
            $superStaff->assignRoleUser([$superRoleModel->id]);
        }

        foreach ($addMenus as $key => $addMenu) {
            if (array_key_exists($key, $menuHistory)) {
                array_push($menuHistory[$key], ...$addMenu);
            } else {
                $menuHistory[$key] = $addMenu;
            }
        }

        // 去掉删除的menu
        foreach ($delMenus as $k => $delMenu) {
            $menuHistory[$k] = array_diff($menuHistory[$k], $delMenus[$k]);
        }


        Config::set($merchantId, Config::KEY_HISTORY_MENU, $menuHistory);

        if (!$this->isInitialized()) {
            Config::set($merchantId, Config::KEY_INIT_ROLE_SYMBOL, 2);
        }

        $this->info('初始化角色完成');
    }

    /**
     * @param $data
     */
    protected function saveRole($data)
    {
        $role = [
            'name' => $data['name'],
            'guard_name' => $data['guard_name'],
            'merchant_id' => MerchantHelper::getMerchantId(),
        ];
        DB::transaction(function () use ($role, $data) {
            $role = Role::updateOrCreate($role);
            $role->giveMenuTo($data['menus']);
        });
    }

    /**
     * @return bool
     */
    protected function isInitialized()
    {
        return Config::model()->getInitRoleSymbol() == 2;
    }

    /**
     * @return array
     */
    protected function getHistoryMenu()
    {
        return (array)Config::model()->getHistoryMenu();
    }

}
