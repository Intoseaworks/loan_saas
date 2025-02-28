<?php

namespace Common\Services\Rbac\Rules;


use Common\Rule\Rule;

class MenuRule extends Rule
{
    /**
     * 创建菜单,编辑菜单
     */
    const SENARIO_CREATE_EDIT = 'create_edit';

    /**
     * 查看
     */
    const SENARIO_SHOW = 'show';

    /**
     * 删除菜单
     */
    const SENARIO_DESTORY = 'destroy';

    /**
     * 菜单推送
     */
    const SENARIO_USER_MENUS = 'user_menus';


    /**
     * @return array
     */
    public function rules()
    {

        return [
            self::SENARIO_CREATE_EDIT => [
                'path' => 'required',
                'name' => 'required',
            ],
            self::SENARIO_SHOW => [
                'path' => 'required',
            ],
            self::SENARIO_DESTORY => [
                'path' => 'required',
            ],
            self::SENARIO_USER_MENUS => [
                'uid' => 'required',
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
            'path' => '路由',
            'uid' => '用户ID',
        ];
    }
}
