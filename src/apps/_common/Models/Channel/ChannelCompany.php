<?php

namespace Common\Models\Channel;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Channel\ChannelCompany
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\ChannelCompany newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\ChannelCompany newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\ChannelCompany query()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\ChannelCompany orderByCustom($column = null, $direction = 'asc')
 */
class ChannelCompany extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'change_company';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];
    /**
     * @var bool
     */
    public $timestamps = false;

    public function textRules()
    {
        return [];
    }

}
