<?php

namespace Admin\Models\Channel;

/**
 * Admin\Models\Channel\ChannelCompany
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\ChannelCompany newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\ChannelCompany newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Channel\ChannelCompany query()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Channel\ChannelCompany orderByCustom($column = null, $direction = 'asc')
 */
class ChannelCompany extends \Common\Models\Channel\ChannelCompany
{

    public function safes()
    {
        return [];
    }

    public function getList($param)
    {
        return $this->paginate();
    }

    /**
     * 根据id获取
     * @param $id
     * @return $this
     */
    public function getOne($id)
    {
        return self::where('id', '=', $id)->first();
    }

}
