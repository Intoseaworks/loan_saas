<?php

namespace Admin\Rules\Channel;

use Admin\Models\Channel\Channel;
use Common\Models\Channel\ChannelCount;
use Common\Rule\Rule;

class ChannelRule extends Rule
{
    /**
     * detail 验证场景
     */
    const SCENARIO_DETAIL = 'detail';
    const SCENARIO_LIST = 'list';
    const SCENARIO_MONITOR = 'monitor';
    const SCENARIO_MONITOR_ITEM = 'monitor_item';
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_DELETE = 'delete';
    const SCENARIO_CHECK = 'check';
    const SCENARIO_STATUS = 'status';
    const SCENARIO_COUNT = 'count';
    const SCENARIO_TOP = 'top';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_LIST => [
                'status' => 'integer|in:' . implode(',', array_keys(Channel::STATUS)),
                'keyword' => 'string',
                'time_start' => 'required_with:time_end|date_format:Y-m-d H:i:s',
                'time_end' => 'required_with:time_start|date_format:Y-m-d H:i:s|after:time_start',
            ],
            self::SCENARIO_MONITOR => [
                'status' => 'integer|in:' . implode(',', array_keys(Channel::STATUS)),
                'keyword' => 'string',
                'time_start' => 'required_with:time_end|date_format:Y-m-d',
                'time_end' => 'required_with:time_start|date_format:Y-m-d',
            ],
            self::SCENARIO_MONITOR_ITEM => [
                'channel_code' => 'required|string|exists:channel,channel_code',
            ],
            self::SCENARIO_CREATE => [
                'page_name' => 'required|string',
                'channel_code' => 'required|string',
                //'sort' => 'required|integer',
                //'cooperation_time' => 'required|date_format:Y-m-d H:i:s',
            ],
            self::SCENARIO_UPDATE => [
                //'sort' => 'required|integer',
                'cooperation_time' => 'required|date_format:Y-m-d H:i:s',
            ],
            self::SCENARIO_DELETE => [
                'id' => 'required|exists:channel,id',
            ],
            self::SCENARIO_DETAIL => [
                'id' => 'required|exists:channel,id',
            ],
            self::SCENARIO_CHECK => [
                'channel_code' => 'required|string|unique:channel,channel_code',
            ],
            self::SCENARIO_STATUS => [
                'id' => 'required|exists:channel,id',
                'status' => 'integer|in:' . implode(',', array_keys(Channel::STATUS)),
            ],
            self::SCENARIO_COUNT => [
                'id' => 'required|exists:channel,id',
                'type' => 'string|in:' . implode(',', array_keys(ChannelCount::COUNT_TYPE)),
            ],
            self::SCENARIO_TOP => [
                'id' => 'required|exists:channel,id',
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
                'channel_code.unique' => '标识码已使用，请重新填写',
            ],
            self::SCENARIO_CHECK => [
                'channel_code.unique' => '标识码已使用，请重新填写',
            ],
            self::SCENARIO_DETAIL => [
                'id.required' => 'id 不能为空',
                'id.exists' => '账户不存在'
            ],
        ];
    }

    public function attributes()
    {
        return [
            'channel_name' => '平台名称',
            'channel_code' => '合作标识码',
            'sort' => '排名设置',
            'cooperation_time' => '合作时间',
        ];
    }
}
