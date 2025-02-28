<?php

namespace Common\Services\Rbac\Rules;

use Common\Rule\Rule;
use Common\Services\Rbac\Models\Role;
use Illuminate\Support\Facades\Validator;

class RoleRule extends Rule
{
    /**
     * 创建角色
     */
    const SENARIO_CREATE = 'create';

    /**
     * 编辑角色
     */
    const SENARIO_EDIT = 'edit';

    /**
     * 查看角色
     */
    const SENARIO_SHOW = 'show';

    /**
     * 删除角色
     */
    const SENARIO_DESTORY = 'destroy';

    /**
     * 根据角色获取权限
     */
    const SENARIO_ROLE_HAS_PERMISSION = 'role_has_permission';

    /**
     * 用户赋值权限
     */
    const SENARIO_ASSIGN_ROLE = 'assign_role';

    /**
     * 清空用户权限
     */
    const SENARIO_DETACH_ROLE = 'detach_role';

    /**
     * 权限验证
     */
    const SENARIO_CHECK_PERMISSION = 'check_permission';

    /**
     * 未授权用户列表
     */
    const SENARIO_UNAUTHORIZED_USER = 'unauthorized_user';

    /**
     * 用户角色
     */
    const SENARIO_USER_ROLE = 'user_role';

    /**
     * @return array
     */
    public function rules()
    {

        Validator::extendImplicit('customRule', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();

            /** @var Role $role */
            $role = Role::findById($data['id']);

            if (!$role->isSuper()) {
                $menus = $data['menus'] ?? null;
                $operations = $data['operations'] ?? null;
                if (empty($menus) && empty($operations)) {
                    return false;
                }
            }
            return true;
        }, '菜单或功能不能为空');

        return [
            self::SENARIO_CREATE => [
                'name' => 'required|max:255',
                'menus' => 'required_without:operations',
                'operations' => 'required_without:menus',
//                'guard_name' => 'required',
            ],
            self::SENARIO_EDIT => [
                'id' => 'required|integer',
                'name' => 'required|max:255',
                'menus' => 'customRule',
                'operations' => 'customRule',
//                'guard_name' => 'required',
            ],
            self::SENARIO_SHOW => [
                'id' => 'required|integer',
            ],
            self::SENARIO_DESTORY => [
                'id' => 'required|integer',
            ],
            self::SENARIO_ROLE_HAS_PERMISSION => [
                'role_ids' => 'required',
            ],
            self::SENARIO_ASSIGN_ROLE => [
                'uid' => 'required',
//                'guard_name' => 'required',
            ],
            self::SENARIO_DETACH_ROLE => [
                'uid' => 'required',
//                'guard_name' => 'required',
            ],
            self::SENARIO_CHECK_PERMISSION => [
                'route' => 'required',
                'uid' => 'required|integer',
                'path' => 'required',
                'guard_name' => 'required',
            ],
            self::SENARIO_UNAUTHORIZED_USER => [
                'role_id' => 'nullable|integer',
//                'guard_name' => 'required',
            ],
            self::SENARIO_USER_ROLE => [
                'uid' => 'required',
//                'guard_name' => 'required',
            ],
        ];

    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => '名称',
            'menus' => '菜单',
            'operations' => '功能',
            'guard_name' => '模块',
            'role_ids' => '角色ID',
            'uid' => '用户ID',
            'route' => '路由',
            'role_id' => '角色ID',
            'path' => '路由path属性',
        ];
    }
}
