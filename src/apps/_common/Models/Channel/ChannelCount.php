<?php

namespace Common\Models\Channel;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;


/**
 * Common\Models\Channel\ChannelCount
 *
 * @property int $id ID
 * @property int $channel_id 渠道ID
 * @property int $register_pv 注册pv
 * @property int $register_uv 注册uv
 * @property int $download_pv 下载pv
 * @property int $download_uv 下载uv
 * @property string|null $count_at 监控时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\ChannelCount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\ChannelCount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\ChannelCount orderByCustom($column = null, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\ChannelCount query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\ChannelCount whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\ChannelCount whereCountAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\ChannelCount whereDownloadPv($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\ChannelCount whereDownloadUv($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\ChannelCount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\ChannelCount whereRegisterPv($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\ChannelCount whereRegisterUv($value)
 * @mixin \Eloquent
 */
class ChannelCount extends Model
{
    use StaticModel;


    const REGISTER_PV = 'register_pv';
    const REGISTER_UV = 'register_uv';
    const DOWNLOAD_PV = 'download_pv';
    const DOWNLOAD_UV = 'download_uv';
    const COUNT_TYPE = [
        self::REGISTER_PV => '注册PV',
        self::DOWNLOAD_PV => '下载PV',
        self::DOWNLOAD_UV => '下载UV',
    ];
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'channel_count';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];

    public function textRules()
    {
        return [];
    }
}
