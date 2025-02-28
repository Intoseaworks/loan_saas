<?php

namespace Common\Services\Rbac\Rules;

use Common\Rule\Rule;

class OperationRule extends Rule
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
     * @return array
     */
    public function rules()
    {

        return [
            self::SENARIO_CREATE => [
                'name' => 'required|max:255',
                'permissions' => 'required',
                'menu_key' => 'required',
                'guard_name' => 'required',
            ],
            self::SENARIO_EDIT => [
                'id' => 'required|integer',
                'name' => 'required|max:255',
                'permissions' => 'required',
                'menu_key' => 'required',
                'guard_name' => 'required',
            ],
            self::SENARIO_SHOW => [
                'id' => 'required|integer',
            ],
            self::SENARIO_DESTORY => [
                'id' => 'required|integer',
            ],
        ];

    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'permissions' => '路由',
            'menu_key' => '菜单',
            'guard_name' => '模块',
        ];
    }
}
