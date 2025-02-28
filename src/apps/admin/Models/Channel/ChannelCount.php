<?php

namespace Admin\Models\Channel;


/**
 * Admin\Models\Channel\ChannelCount
 *
 * @property int $id ID
 * @property int $channel_id 渠道ID
 * @property int $register_pv 注册pv
 * @property int $register_uv 注册uv
 * @property int $download_pv 下载pv
 * @property int $download_uv 下载uv
 * @property string|null $count_at 监控时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\ChannelCount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\ChannelCount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\ChannelCount orderByCustom($column = null, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\ChannelCount query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\ChannelCount whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\ChannelCount whereCountAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\ChannelCount whereDownloadPv($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\ChannelCount whereDownloadUv($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\ChannelCount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\ChannelCount whereRegisterPv($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\ChannelCount whereRegisterUv($value)
 * @mixin \Eloquent
 */
class ChannelCount extends \Common\Models\Channel\ChannelCount
{

    const SCENARIO_LIST = 'list';
    const SCENARIO_CREATE = 'create';


    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'channel_name',
                'channel_code',
                'cooperation_time',
                'sort',
                'status',
                'url',
            ],

        ];
    }

    public function texts()
    {
        return [
            self::SCENARIO_LIST => [
                'id',
                'channel_name',
                'channel_code',
                'cooperation_time',
                'sort',
                'status',
                'url',
            ],
        ];
    }

    public function textRules()
    {
        return [
        ];
    }
}
