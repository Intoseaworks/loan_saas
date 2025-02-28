<?php

namespace Api\Rules\Action;

use Common\Rule\Rule;

class ActionLogRule extends Rule
{

    const SCENARIO_CREATE = 'create';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_CREATE => [
                'content' => 'string|max:5000',
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
            self::SCENARIO_CREATE => [
                'id.required' => 'id 不能为空',
                'id.exists' => '用户不存在'
            ],
        ];
    }
}
