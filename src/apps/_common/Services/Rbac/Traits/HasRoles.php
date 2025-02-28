<?php

namespace Common\Services\Rbac\Traits;

use Common\Services\Rbac\Models\Menu;
use Common\Services\Rbac\Models\Permission;
use Common\Services\Rbac\Models\Role;
use Common\Services\Rbac\Utils\Helper;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles as spatieHasRoles;

trait HasRoles
{
    use spatieHasRoles;

    /**
     * 验证用户权限
     *
     * @param $route
     * @param $path
     * @return bool
     * @throws \Exception
     */
    public function checkPermission($route, $path)
    {
        //检查rbac是否关闭
        if (Helper::isRbacClosed()) {
            return true;
        }

        //检查是否是超级管理员
        if (Helper::isSuperAdmin()) {
            return true;
        }

        $userRoleIds = $this->roles
            ->pluck('id')
            ->toArray();

        //通过菜单判断权限
        $menu = Menu::wherePath($path)->first();
        if (!$menu) {
            return true;
            throw new \Exception('请求错误,菜单不存在', 500);
        }
        //获取菜单对应的角色
        $menuRoleIds = $menu->roles
            ->pluck('id')
            ->toArray();
        //获取父节点对应的role
        $menuRoleIds = array_merge($menuRoleIds, Menu::getParentRoles($menu->parent_path));
        //判断用户权限
        if (count(array_intersect($userRoleIds, $menuRoleIds)) > 0) {
            return true;
        }

        //根据功能判断权限
        $permission = Permission::whereName($route)->first();
        if (!$permission) {
            throw new \Exception($route . ' 接口未找到', 500);
        }
        $operationRoleIds = [];
        $operations = $permission->operations;
        foreach ($operations as $operation) {
            $roleIds = $operation->roles
                ->pluck('id')
                ->toArray();
            if ($roleIds) {
                array_push($operationRoleIds, ...$roleIds);
            }
        }
        //判断用户权限
        if (count(array_intersect($userRoleIds, $operationRoleIds)) > 0) {
            return true;
        }

        return false;
    }

    /**
     * 给角色赋值
     *
     * @param array $roleIds
     * @param bool $sync
     */
    public function assignRoleUser(array $roleIds, $sync = false)
    {
        $roleIds = array_unique(array_filter($roleIds));
        if ($roleIds) {
            //查找角色
            $roles = Role::whereIn('id', $roleIds)->get();
            if ($sync) {
                $this->syncRoles($roles);
            } else {
                $this->assignRole($roles);
            }
        }
    }

    /**
     * 清除用户所有角色
     */
    public function removeAllRoles()
    {
        // @phan-suppress-next-line PhanUndeclaredProperty
        foreach ($this->roles as $role) {
            $this->removeRole($role);
        }
    }

    /**
     * Return all the menus the model has via roles.
     */
    public function getMenusViaRoles(): Collection
    {
        // @phan-suppress-next-line PhanUndeclaredMethod
        return $this->load('roles', 'roles.menus')
            ->roles->flatMap(function ($role) {
                return $role->menus;
            })->sort()->values();
    }

    /**
     * Return all the operations the model has via roles.
     */
    public function getOperationsViaRoles(): Collection
    {
        // @phan-suppress-next-line PhanUndeclaredMethod
        return $this->load('roles', 'roles.operations')
            ->roles->flatMap(function ($role) {
                return $role->operations;
            })->sort()->values();
    }

    /**
     * Return all the menus the model has via roles.
     */
    public function getMenusViaOperations(): Collection
    {
        // @phan-suppress-next-line PhanUndeclaredMethod
        return $this->load('roles', 'roles.operations')
            ->roles->flatMap(function ($role) {
                return $role->operations;
            })->sort()->values();
    }

    /**
     * A model may have multiple roles.
     */
    public function roles(): MorphToMany
    {
        return $this->morphToMany(
            config('permission.models.role'),
            'model',
            Helper::getDatabaseName(Helper::getConnection()) . '.' . config('permission.table_names.model_has_roles'),
            'model_id',
            'role_id'
        );
    }
}
