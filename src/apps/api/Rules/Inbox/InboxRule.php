<?php

namespace Api\Rules\Inbox;

use Common\Rule\Rule;

class InboxRule extends Rule
{

    const SCENARIO_INDEX = 'index';
    const SCENARIO_GET = 'get';
    const SCENARIO_SET_READ = 'set_read';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_GET => [
                'type' => 'required|string|in:inbox,notice',
            ],
            self::SCENARIO_SET_READ => [
                'id' => 'required|array',
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
            self::SCENARIO_GET => [
                'type.required' => t('类型不能为空', 'rule'),
                'type.in' => t('类型不正确', 'rule'),
            ],
        ];
    }
}
