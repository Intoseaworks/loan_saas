<?php

namespace Api\Rules\SaasMaster;

use Admin\Models\Staff\Staff;
use Common\Rule\Rule;

class MerchantRule extends Rule
{
    const METHOD_INIT_MERCHANT = 'init_merchant';
    const METHOD_CREATE_INIT_MERCHANT = 'create_init_merchant';
    const METHOD_UPD_PASSWORD = 'upd_password';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::METHOD_INIT_MERCHANT => [
                'merchantId' => 'required',
                'adminUsername' => 'required|regex:/^[a-z0-9]+$/i|unique:staff,username,null,id,status,!' . Staff::STATUS_DELETE,
                'adminPassword' => 'required',
            ],
            self::METHOD_CREATE_INIT_MERCHANT => [
                'merchantId' => 'required',
            ],
            self::METHOD_UPD_PASSWORD => [
                'merchantId' => 'required',
                'adminPassword' => 'required',
            ],
        ];
    }

    public function messages()
    {
        return [
            self::METHOD_INIT_MERCHANT => [
                'adminUsername.required' => '请填写账户名',
                'adminUsername.regex' => '请设置英文+数字格式账户名',
                'adminPassword.required' => '请填写密码',
                'adminUsername.unique' => '商户账号名已存在',
            ],
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
