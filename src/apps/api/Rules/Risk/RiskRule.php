<?php

namespace Api\Rules\Risk;

use Api\Models\User\UserAuth;
use Common\Rule\Rule;

class RiskRule extends Rule
{
    /**
     * detail 验证场景
     */
    const METHOD_AUTH_STATUS = 'auth_status';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::METHOD_AUTH_STATUS => [
                'authName' => 'required|in:' . implode(',', UserAuth::AUTH_TYPE),
                'userId' => 'required|exists:user,id',
                'authStatus' => 'required|in:' . implode(',', array_keys(UserAuth::AUTH_STATUS)),
                'time' => 'required|date_format:Y-m-d H:i:s',
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
            self::METHOD_AUTH_STATUS => [
                'authName.required' => 'auth 不能为空',
                'authName.in' => '类型不正确',
                'userId.required' => 'id 不能为空',
                'userId.exists' => '记录不存在',
            ],
        ];
    }

    public function attributes()
    {
        return [

        ];
    }
}
