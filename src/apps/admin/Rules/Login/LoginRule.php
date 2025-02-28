<?php

namespace Admin\Rules\Login;

use Admin\Models\Staff\Staff;
use Common\Rule\Rule;

class LoginRule extends Rule
{

    const SCENARIO_LOGIN = 'login';

    const SCENARIO_DING_LOGIN = 'ding_login';

    const SCENARIO_PWD_LOGIN = 'pwd_login';

    const SCENARIO_LOGOUT = 'logout';

    protected $id = null;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'login' => [
                'redirect_uri' => 'url',
            ],
            'ding_login' => [

            ],
            'pwd_login' => [
                'username' => 'required|string',
                'password' => 'string',
            ],
            'logout' => [

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
            'detail' => [
                'id.required' => 'id 不能为空',
                'id.exists' => '用户不存在'
            ],
            'create' => [
                'username.required' => '请填写用户名',
                'password.required' => '请填写密码',
            ],
            'update' => [
                'id.required' => 'id 不能为空',
                'id.exists' => '用户不存在',
                'username.required' => '请填写用户名',
                'status.in' => 'status 参数不正确'
            ],
            'delete' => [
                'id.required' => 'id 不能为空',
                'id.exists' => '用户不存在或已禁用'
            ],
        ];
    }

    public function setId($id)
    {
        $this->id = $id;
    }
}
