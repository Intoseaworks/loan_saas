<?php

namespace Admin\Rules\Staff;

use Admin\Models\Staff\Staff;
use Common\Rule\Rule;

class StaffRule extends Rule
{
    /**
     * 验证场景 create
     */
    const SCENARIO_CREATE = 'create';
    /**
     * 验证场景 update
     */
    const SCENARIO_UPDATE = 'update';
    /**
     * detail 验证场景
     */
    const SCENARIO_DETAIL = 'detail';
    /**
     * 验证场景 删除
     */
    const SCENARIO_DELETE = 'delete';
    /**
     * 验证场景 禁用/启用
     */
    const SCENARIO_DISABLE_ENABLE = 'disable_enable';
    /**
     * 验证场景 设置密码
     */
    const SCENARIO_PASSWORD_SETTING = 'password_setting';
    /**
     * 验证场景 权限赋值
     */
    const SCENARIO_ASSIGN_ROLE = 'assign_role';
    /**
     * 验证场景 用户名验重
     */
    const SCENARIO_VERIFY_REPEAT = 'verify_repeat';

    /**
     * 验证场景 修改个人密码
     */
    const SCENARIO_EDIT_PASSWORD = 'edit_password';

    protected $id = null;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_DETAIL => [
                'id' => 'required|exists:staff,id',
            ],
            self::SCENARIO_CREATE => [
                'username' => 'required|regex:/^[a-z0-9]+$/i|unique:staff,username,null,id,status,!' . Staff::STATUS_DELETE,
                'nickname' => 'required|string',
                'password' => 'required|',
                'role_ids' => 'required|string',
            ],
            self::SCENARIO_DELETE => [
                'id' => 'required|exists:staff,id,status,!' . Staff::STATUS_DELETE,
            ],
            self::SCENARIO_DISABLE_ENABLE => [
                'id' => 'required|exists:staff,id,status,!' . Staff::STATUS_DELETE,
                'status' => 'required|in:' . Staff::STATUS_NORMAL . ',' . Staff::STATUS_DISABLE,
            ],
            self::SCENARIO_PASSWORD_SETTING => [
                'id' => 'required|exists:staff,id,status,!' . Staff::STATUS_DELETE,
                'password' => 'string',
                'unbind_ding' => 'string',
            ],
            self::SCENARIO_ASSIGN_ROLE => [
                'id' => 'required|exists:staff,id,status,!' . Staff::STATUS_DELETE,
                'role_ids' => 'string',
                // 'nickname' => 'string|regex:/^[\x{4e00}-\x{9fa5}]+$/u',
                'nickname' => 'string',
            ],
            self::SCENARIO_VERIFY_REPEAT => [
                'username' => 'required|regex:/^[a-z0-9]+$/i|unique:staff,username,null,id,status,!' . Staff::STATUS_DELETE,
            ],
            self::SCENARIO_EDIT_PASSWORD => [
                'old_password' => 'required',
                'new_password' => 'required',
                'again_password' => 'required',#|regex:/^[a-z0-9]+$/i
            ],
        ];
    }

    public function getStatusString()
    {
        return implode(',', array_keys(Staff::STATUS));
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
            self::SCENARIO_DETAIL => [
                'id.required' => 'id 不能为空',
                'id.exists' => '账户不存在'
            ],
            self::SCENARIO_CREATE => [
                'username.required' => '请填写账户名',
                'username.regex' => '请设置英文+数字格式账户名',
                'password.required' => '请填写密码',
                'nickname.required' => '请填写昵称',
                'nickname.regex' => '请填写中文昵称',
            ],
            self::SCENARIO_UPDATE => [
                'id.required' => 'id 不能为空',
                'id.exists' => '账户不存在',
                'username.required' => '请填写用户名',
                'status.in' => 'status 参数不正确',
            ],
            self::SCENARIO_DELETE => [
                'id.required' => 'id 不能为空',
                'id.exists' => '账户不存在或已删除',
            ],
            self::SCENARIO_DISABLE_ENABLE => [
                'id.exists' => '账户状态不正确',
            ],
            self::SCENARIO_ASSIGN_ROLE => [
                'nickname.regex' => '请填写中文昵称',
            ],
            self::SCENARIO_VERIFY_REPEAT => [
                'username.unique' => '账号名已注册，请重新填写',
                'username.regex' => '请设置英文+数字格式账号名',
            ],
        ];
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function attributes()
    {
        return [
            'username' => '账户名',
            'role_ids' => '角色',
            'password' => '密码',
        ];
    }
}
