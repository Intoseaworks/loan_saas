<?php

namespace Api\Rules\Common;

use Common\Models\Channel\ChannelCount;
use Common\Rule\Rule;

class ChannelRule extends Rule
{
    /**
     * detail 验证场景
     */
    const SCENARIO_COUNT = 'count';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_COUNT => [
                'id' => 'required|exists:channel,channel_code',
                'type' => 'string|in:' . implode(',', array_keys(ChannelCount::COUNT_TYPE)),
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
